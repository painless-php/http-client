<?php

namespace PainlessPHP\Http\Client\Adapter;

use Generator;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlMultiHandler;
use GuzzleHttp\Exception\TooManyRedirectsException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use PainlessPHP\Http\Client\ClientMiddlewareStack;
use PainlessPHP\Http\Client\ClientRequest;
use PainlessPHP\Http\Client\ClientResponse;
use PainlessPHP\Http\Client\Contract\ClientAdapter;
use PainlessPHP\Http\Client\Exception\ClientException;
use PainlessPHP\Http\Client\Exception\MessageException;
use PainlessPHP\Http\Client\Exception\NetworkException;
use PainlessPHP\Http\Client\Internal\ClosureClientRequestProcessor;
use PainlessPHP\Http\Client\Redirection;
use PainlessPHP\Http\Client\RequestResolution;
use PainlessPHP\Http\Client\RequestResolutionCollection;
use PainlessPHP\Http\Message\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzleClientAdapter implements ClientAdapter
{
    private const array guzzleHeaders = [
        'X-Guzzle-Redirect-History',
        'X-Guzzle-Redirect-Status-History'
    ];

    private ?Client $guzzle;

    public function __construct()
    {
        $this->guzzle = $this->createSyncClient();
    }

    private function createSyncClient() : Client
    {
        return new Client;
    }

    private function createAsyncClient(ClientMiddlewareStack $middlewares) : Client
    {
        $stack = HandlerStack::create(new CurlMultiHandler);
        $stack->push($this->createAsyncMiddleware($middlewares));
        return new Client(['handler' => $stack]);
    }

    /**
     * Do not attempt serializing client since it contains closures which
     * cannot be serialized
     *
     */
    public function __sleep() : array
    {
        $this->guzzle = null;
        return [];
    }

    /**
     * Recreate client when unserialized
     *
     */
    public function __wakeup()
    {
        $this->guzzle = $this->createSyncClient();
    }

    public function sendRequest(ClientRequest $request) : ClientResponse
    {
        try {
            $options = $this->createRequestOptions($request);
            $guzzleResponse = $this->guzzle->send($request, $options);
            return $this->createResponse($request, $guzzleResponse);
        }
        catch(TransferException $e) {
            throw $this->createException($request, $e);
        }
    }

    public function sendRequests(
        array $requests,
        ClientMiddlewareStack $middlewares,
        ?callable $beforeRequest = null,
        ?callable $afterResponse = null,
        ?int $concurrency = null,
    ): RequestResolutionCollection
    {
        $client = $this->createAsyncClient($middlewares);

        // Transform requests to promises
        $promises = $this->createAsyncRequests($client, $requests, $beforeRequest);
        $resolutions = [];

        $pool = new Pool($client, $promises, [
            'concurrency' => $concurrency ?? count($promises),
            'fulfilled' => function($response, $index) use ($requests, &$resolutions, $afterResponse) {
                $request = $requests[$index];
                $response = $this->createResponse($request, $response);
                $response = $afterResponse === null ? $response : $afterResponse($response);
                $resolutions[] = new RequestResolution($request, $response);
            },
            'rejected' => function($exception, $index) use ($requests, &$resolutions) {
                $request = $requests[$index];
                $exception = $this->createException($request, $exception);
                $resolutions[] = new RequestResolution($request, $exception);
            }
        ]);

        // Wait for requests to resolve
        $pool->promise()->wait();

        return new RequestResolutionCollection($resolutions);
    }

    private function createAsyncMiddleware(ClientMiddlewareStack $middlewares)
    {
        return function(callable $handler) use($middlewares) {
            return function(RequestInterface $guzzleRequest, array $options) use($handler, $middlewares) {
                /** @var PromiseInterface $promise  */
                $promise = $handler($guzzleRequest, $options);

                return $promise->then(function(ResponseInterface $guzzleResponse) use($options, $middlewares) {
                    $request = $options['original'];
                    return $middlewares->apply($request, new ClosureClientRequestProcessor(function() use($request, $guzzleResponse) {
                        return $this->createResponse($request, $guzzleResponse);
                    }));
                });

                return $promise;
            };
        };
    }

    private function createAsyncRequests(Client $client, array|Generator $requests, callable $beforeRequest) : array|callable
    {
        if(is_array($requests)) {
            return array_map(
                fn(ClientRequest $request) => $this->createAsyncRequest($client, $request, $beforeRequest),
                $requests
            );
        }
        return function() use($client, $requests, $beforeRequest) {
            foreach($requests as $request) {
                yield $this->createAsyncRequest($client, $request, $beforeRequest);
            }
        };
    }

    private function createAsyncRequest(Client $client, ClientRequest $request, ?callable $beforeRequest)
    {
        $request = $beforeRequest === null ? $request : $beforeRequest($request);
        return fn() => $client->sendAsync($request, $this->createRequestOptions($request));
    }

    private function createResponse(ClientRequest $request, ResponseInterface $response) : ClientResponse
    {
        return new ClientResponse(
            request: $request,
            status: $response->getStatusCode(),
            body: $response->getBody(),
            headers: $this->createResponseHeaders($response),
            redirections: $this->createRedirections($request->getUri(), $response)
        );
    }

    private function createResponseHeaders(ResponseInterface $response) : array
    {
        // Strip guzzle headers from the response headers
        return array_diff_key($response->getHeaders(), self::guzzleHeaders);
    }

    /**
     *  Create redirection objects from guzzle redirection headers
     *
     */
    private function createRedirections(Uri $source, ResponseInterface $response) : array
    {
        $uris = $response->getHeader('X-Guzzle-Redirect-History');
        $codes = $response->getHeader('X-Guzzle-Redirect-Status-History');
        $redirections = [];
        foreach($uris as $index => $uri) {
            $redirections[] = new Redirection($source, $uri, intval($codes[$index]));
        }
        return $redirections;
    }

    /**
     * Create request options for guzzle from a request object
     *
     */
    private function createRequestOptions(ClientRequest $request) : array
    {
        $settings = $request->getSettings();
        $options = [
            'timeout' => $settings->getTimeout(),
            'http_errors' => false,
            'allow_redirects' => [
                'max' => $settings->getMaxRedirections(),
                'track_redirects' => true
            ],
            'original' => $request
        ];
        return $options;
    }

    private function createException(ClientRequest $request, TransferException $original) : MessageException
    {
        if($original instanceof ConnectException) {
            /* Handle curl errors if applicable */
            $curlCode = $original->getHandlerContext()['errno'];
            $curlMessage = curl_strerror($curlCode);
            $msg = "$curlMessage (connection error code $curlCode)";
            return new NetworkException($request, $msg, $curlCode, $original);
        }

        if($original instanceof TooManyRedirectsException) {
            $msg = "Too many redirections";
            return new NetworkException($request, $msg, 0, $original);
        }

        $type = get_class($original);
        $msg = "Unhandled exception type '$type'";
        return new ClientException($msg);
    }
}
