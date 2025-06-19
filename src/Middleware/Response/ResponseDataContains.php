<?php

namespace PainlessPHP\Http\Client\Middleware\Response;

use PainlessPHP\Http\Client\ClientResponse;
use PainlessPHP\Http\Client\Contract\ResponseMiddleware;
use PainlessPHP\Http\Client\Exception\ResponseContentException;
use PainlessPHP\Http\Client\ParsedBody;

class ResponseDataContains implements ResponseMiddleware
{
    public function __construct(private string $path)
    {

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

        return $response;
    }
}
