<?php

namespace PainlessPHP\Http\Client\Middleware\Client;

use PainlessPHP\Http\Client\ClientRequest;
use PainlessPHP\Http\Client\ClientResponse;
use PainlessPHP\Http\Client\Concern\IntoClientRequestProcessor;
use PainlessPHP\Http\Client\Contract\ClientMiddleware;
use PainlessPHP\Http\Client\Contract\ClientRequestProcessor;
use PainlessPHP\Http\Client\Exception\MessageException;
use PainlessPHP\Http\Client\ParsedBody;
use Psr\Log\LoggerInterface;

class LogTraffic implements ClientMiddleware
{
    use IntoClientRequestProcessor;

    /**
     * @param array<string,string> $placeholders
     */
    public function __construct(
        private LoggerInterface $logger,
        private mixed $logLevel,
        private string $requestFormat = 'Sending {request:method} request to {request:uri}',
        private string $responseFormat = 'Received {status:code} response',
        private string $errorFormat = 'Failed to send request: {exception:message}',
        private array $placeholders = []
    )
    {
    }

    public function apply(ClientRequest $request, ClientRequestProcessor $next): ClientResponse
    {
        dd('apply');
        $this->logRequest($request);

        try {
            $response = $next->process($request);
            $this->logResponse($response);
            return $response;
        }
        catch(MessageException $e) {
            // Log exception related to sending the message
            $this->logger->error(
                $this->replacePlaceholders($this->errorFormat, $request, [
                    'exception:message' => $e->getMessage(),
                    'exception:code' => $e->getCode()
                ]),
                $this->resolveLoggerContext($request)
            );
            throw $e;
        }
    }

    private function logRequest(ClientRequest $request)
    {
        var_dump($this->replacePlaceholders($this->requestFormat, $request));
        $this->logger->log(
            $this->logLevel,
            $this->replacePlaceholders($this->requestFormat, $request),
            $this->resolveLoggerContext($request)
        );
    }

    private function logResponse(ClientResponse $response)
    {
        var_dump($this->replacePlaceholders($this->responseFormat, $response));
        $this->logger->log(
            $this->logLevel,
            $this->replacePlaceholders($this->responseFormat, $response),
            $this->resolveLoggerContext($response)
        );
    }

    private function resolveLoggerContext(ClientRequest|ClientResponse $context) : array
    {
        $name = $context instanceof ClientRequest
        ? 'request'
        : 'response';

        $body = $context->getBody();

        $content = $body instanceof ParsedBody
        ? $body->getParsedContent()
        : $body->getContents();

        return [
            "{$name}_body" => $content
        ];
    }

    private function replacePlaceholders(string $message, ClientRequest|ClientResponse $context) : string
    {
        foreach ($this->resolvePlaceholders($context) as $name => $value) {
            $message = str_replace('{' . $name . '}', $value, $message);
        }

        return $message;
    }

    /**
     * Get all placeholder resolvers for a given message context
     *
     */
    private function resolvePlaceholders(ClientRequest|ClientResponse $context) : array
    {
        $placeholders = [];

        if($context instanceof ClientResponse) {
            $placeholders = array_merge($placeholders, [
                'status:code' => $context->getStatus()->getCode(),
                'status:reason_phrase' => $context->getStatus()->getReasonPhrase(),
                'status:description' => $context->getStatus()->getDescription()
            ]);
            $context = $context->getRequest();
        }

        if($context instanceof ClientRequest) {
            $placeholders = array_merge($placeholders, [
                'request:uri' => (string)$context->getUri(),
                'request:method' => $context->getMethod(),
                ...array_map(fn(string $name) => "attribute:$name", $context->getAttributes())
            ]);
        }

        return array_merge($placeholders, $this->placeholders);
    }
}
