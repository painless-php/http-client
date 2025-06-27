<?php

namespace PainlessPHP\Http\Client\Middleware\Request;

use PainlessPHP\Http\Client\ClientRequest;
use PainlessPHP\Http\Client\Contract\RequestMiddleware;
use PainlessPHP\Http\Client\Internal\Arr;
use PainlessPHP\Http\Client\Internal\Placeholder;
use PainlessPHP\Http\Client\ParsedBody;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class LogRequest implements RequestMiddleware
{
    /**
     * @param array<string,string> $placeholders
     */
    public function __construct(
        private LoggerInterface $logger,
        private mixed $logLevel = LogLevel::DEBUG,
        private string $format = 'Sending {request:method} request to {request:uri}',
        private array $placeholders = []
    )
    {
    }

    public function apply(ClientRequest $response): ClientRequest
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

    /**
     * The reason this is public is because it's used by LogResponse.
     * Could be moved to a dedicated class along the lines of "RequestLoggerPlaceholderResolver"
     *
     */
    public static function resolvePlaceholders(ClientRequest $request) : array
    {
        return [
            'request:uri' => (string)$request->getUri(),
            'request:method' => $request->getMethod(),
            ...Arr::mapWithKeys(
                $request->getAttributes(),
                fn(string $name) => ["attribute:$name", $request->getAttribute($name)]
            )
        ];
    }

    protected function resolveLoggerContext(ClientRequest $context) : array
    {
        $body = $context->getBody();

        $content = $body instanceof ParsedBody
        ? $body->getParsedContent()
        : $body->getContents();

        return [
            "request_body" => $content
        ];
    }
}
