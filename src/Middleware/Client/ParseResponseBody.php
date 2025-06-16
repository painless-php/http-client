<?php

namespace PainlessPHP\Http\Client\Middleware\Response;

use PainlessPHP\Http\Client\ClientRequest;
use PainlessPHP\Http\Client\ClientResponse;
use PainlessPHP\Http\Client\Contract\BodyParser;
use PainlessPHP\Http\Client\Contract\ClientMiddleware;
use PainlessPHP\Http\Client\Contract\ClientRequestProcessor;
use PainlessPHP\Http\Client\Exception\ResponseParsingException;
use PainlessPHP\Http\Client\ParsedBody;
use PainlessPHP\Http\Client\Parser\JsonBodyParser;
use PainlessPHP\Http\Client\Parser\XmlBodyParser;

class ParseResponseBody implements ClientMiddleware
{
    private const defaultParsers = [
        'application/json' => JsonBodyParser ::class,
        'application/xml'  => XmlBodyParser::class
    ];

    /**
     * @var array<BodyParser>
     */
    protected array $parsers;

    /**
     * @param array<BodyParser>|null $parsers
     */
    public function __construct(?array $parsers = null)
    {
        $this->setParsers(...($parsers === null ? self::getDefaultParsers() : $parsers));
    }

    private static function getDefaultParsers() : array
    {
        return array_map(fn($class) => new $class, self::defaultParsers);
    }

    /**
     * The variadic type acts as a type guard for array items
     *
     */
    private function setParsers(BodyParser ...$parsers)
    {
        $this->parsers = $parsers;
    }

    /**
     * Make response body an instance of ParsedBody
     *
     */
    public function apply(ClientRequest $request, ClientRequestProcessor $next) : ClientResponse
    {
        $response = $next->process($request);
        $contentType = $response->getHeaderLine('content-type');
        $parser = null;

        foreach($this->parsers as $type => $typeParser) {
            if(mb_strpos($contentType, $type) !== false) {
                $parser = $typeParser;
                break;
            }
        }

        if($parser === null) {
            return $response;
        }

        $body = $response->getBody();

        try {
            $body = new ParsedBody($body, $parser->parseBody($body));
            return $response->withBody($body);
        }
        catch(ResponseParsingException $e) {
            // Wrap parser exception within a new error message
            $body = new ParsedBody($body, null);
            $msg = "Could not parse response body with content-type $contentType:" . PHP_EOL . $e->getMessage();
            $exception = new ResponseParsingException($msg, 0, $e);

            return $response->withBody($body)
                ->withException($exception);
        }
    }
}
