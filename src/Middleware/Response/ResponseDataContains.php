<?php

namespace PainlessPHP\Http\Client\Middleware\Response;

use PainlessPHP\Http\Client\ClientResponse;
use PainlessPHP\Http\Client\Contract\ResponseMiddleware;
use PainlessPHP\Http\Client\Exception\ResponseContentException;
use PainlessPHP\Http\Client\Internal\Arr;
use PainlessPHP\Http\Client\ParsedBody;

class ResponseDataContains implements ResponseMiddleware
{
    /**
     * @var array<string> $paths
     */
    private array $paths;

    public function __construct(string|array $paths, private string $pathSeparator = '.')
    {
        if(is_array($paths)) {
            $this->setPaths(...$paths);
        }
        else {
            $this->setPaths($paths);
        }
    }

    /**
     * Type guard
     *
     */
    private function setPaths(string ...$paths)
    {
        $this->paths = $paths;
    }

    public function apply(ClientResponse $response): ClientResponse
    {
        $body = $response->getBody();
        $class = get_class($this);

        if(! ($body instanceof ParsedBody)) {
            $msg = "Response body needs to be parsed to use $class";
            return $response->withException(new ResponseContentException($msg));
        }

        if(! is_array($body->getParsedContent())) {
            $msg = "Parsed response needs to be an array to use $class";
            return $response->withException(new ResponseContentException($msg));
        }

        foreach($this->paths as $path) {
            if(! Arr::pathExists($body->getParsedContent(), $path, $this->pathSeparator)) {
                $msg = "Parsed response body does not contain expected path '$path'";
                $response = $response->withException(new ResponseContentException($msg));
            }
        }
        return $response;
    }
}
