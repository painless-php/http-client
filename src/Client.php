<?php

namespace PainlessPHP\Http\Client;

use Generator;
use PainlessPHP\Http\Client\Contract\ClientAdapter;
use PainlessPHP\Http\Client\Exception\MessageException;
use PainlessPHP\Http\Client\Exception\ResponseException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\RequestFactoryInterface;

/**
 * A psr-18 compliant http client
 *
 */
class Client implements ClientInterface, RequestFactoryInterface
{
    private ClientAdapter $adapter;
    private RequestSettings $settings;
    private RequestMiddlewareStack $requestMiddlewares;
    private ResponseMiddlewareStack $responseMiddlewares;

    public function __construct(
        ClientAdapter $adapter,
        RequestSettings|array $settings = [],
        RequestMiddlewareStack|array $requestMiddlewares = [],
        ResponseMiddlewareStack|array $responseMiddlewares = []
    ) {
        $this->adapter = $adapter;
        $this->setSettings($settings);
        $this->setRequestMiddlewares($requestMiddlewares);
        $this->setResponseMiddlewares($responseMiddlewares);
    }

    private function setSettings(RequestSettings|array $settings)
    {
        if(is_array($settings)) {
            $settings = RequestSettings::createFromArray($settings);
        }
        $this->settings = $settings;
    }

    private function setRequestMiddlewares(RequestMiddlewareStack|array $requestMiddlewares)
    {
        if(is_array($requestMiddlewares)) {
            $requestMiddlewares = RequestMiddlewareStack::createFromArray($requestMiddlewares);
        }
        $this->requestMiddlewares = $requestMiddlewares;
    }

    private function setResponseMiddlewares(ResponseMiddlewareStack|array $responseMiddlewares)
    {
        if(is_array($responseMiddlewares)) {
            $responseMiddlewares = ResponseMiddlewareStack::createFromArray($responseMiddlewares);
        }
        $this->responseMiddlewares = $responseMiddlewares;
    }

    /**
     * Create a request, PSR-17
     *
     * @return ClientRequest (RequestInterface)
     *
     */
    public function createRequest(string $method, $uri, $body = null, $headers = null) : ClientRequest
    {
        return new ClientRequest(
            method: $method,
            uri: $uri,
            body: $body,
            headers: $headers ?? []
        );
    }

    /**
     * Create and send a request
     *
     */
    public function request(string $method, $uri, $body = null, $headers = null) : ClientResponse
    {
        $request = $this->createRequest($method, $uri, $body, $headers);
        return $this->sendRequest($request);
    }

    /**
     *
     * Send a request, PSR-18
     *
     * @throws MessageException
     * @throws ResponseException Only when @$throwResponseExceptions === true, not part of the psr spec
     *
     */
    public function sendRequest(RequestInterface $request, bool $throwResponseExceptions = false) : ClientResponse
    {
        /* Convert request interface into ClientRequest */
        if(! ($request instanceof ClientRequest)) {
            $request = new ClientRequest(
                $request->getMethod(),
                $request->getUri(),
                $request->getBody(),
                $request->getHeaders()
            );
        }

        $request = $this->beforeRequest($request);
        $response = $this->adapter->sendRequest($request);
        $response = $this->afterResponse($response);

        if($throwResponseExceptions) {
            $response->throwExceptions();
        }

        return $response;
    }

    public function sendRequests(array|Generator $requests) : RequestResolutionCollection
    {
        return $this->adapter->sendRequests(
            requests: $requests,
            beforeRequest: fn(ClientRequest $request) => $this->beforeRequest($request),
            afterResponse: fn(ClientResponse $response) => $this->afterResponse($response)
        );
    }

    private function beforeRequest(ClientRequest $request) : ClientRequest
    {
        // Apply the client's settings to the request
        $request = $request->withSettings($this->settings);

        // Apply client request middlewares before sending the request
        $request = $this->requestMiddlewares->apply($request);

        return $request;
    }

    private function afterResponse(ClientResponse $response) : ClientResponse
    {
        // Apply client's response middlewares to the response
        $response = $this->responseMiddlewares->apply($response);

        // Apply request's own response middlewares
        $response = $response->getRequest()->getResponseMiddlewareStack()->apply($response);

        return $response;
    }
}
