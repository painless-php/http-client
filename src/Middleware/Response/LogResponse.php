<?php

namespace PainlessPHP\Http\Client\Middleware\Response;

use PainlessPHP\Http\Client\ClientResponse;
use PainlessPHP\Http\Client\Contract\ResponseMiddleware;
use PainlessPHP\Http\Client\Internal\Placeholder;
use PainlessPHP\Http\Client\Middleware\Request\LogRequest;
use PainlessPHP\Http\Client\ParsedBody;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class LogResponse implements ResponseMiddleware
{
    /**
     * @param array<string,string> $placeholders
     */
    public function __construct(
        private LoggerInterface $logger,
        private mixed $logLevel = LogLevel::DEBUG,
        private string $format = 'Received {status:code} response',
        private array $placeholders = []
    )
    {
    }

    public function apply(ClientResponse $response): ClientResponse
    {
        $placeholders = [
            ...self::resolvePlaceholders($response),
            ...$this->placeholders
        ];

        $this->logger->log(
            $this->logLevel,
            Placeholder::replaceAll($this->format, $placeholders),
            $this->resolveLoggerContext($response)
        );
        return $response;
    }

    private static function resolvePlaceholders(ClientResponse $response) : array
    {
        return [
            ...LogRequest::resolvePlaceholders($response->getRequest()),
            'status:code' => $response->getStatus()->getCode(),
            'status:reason_phrase' => $response->getStatus()->getReasonPhrase(),
            'status:description' => $response->getStatus()->getDescription(),
        ];
    }

    private function resolveLoggerContext(ClientResponse $context) : array
    {
        $body = $context->getBody();

        $content = $body instanceof ParsedBody
        ? $body->getParsedContent()
        : $body->getContents();

        return [
            "response_body" => $content
        ];
    }
}
