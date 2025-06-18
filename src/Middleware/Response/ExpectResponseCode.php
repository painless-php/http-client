<?php

namespace PainlessPHP\Http\Client\Middleware\Response;

use PainlessPHP\Http\Client\ClientResponse;
use PainlessPHP\Http\Client\Contract\ResponseMiddleware;
use PainlessPHP\Http\Client\Exception\UnexpectedStatusCodeException;

class ExpectResponseCode implements ResponseMiddleware
{
    public function __construct(private int $code)
    {
    }

    public function apply(ClientResponse $response): ClientResponse
    {
        if($response->getStatusCode() !== $this->code) {
            $msg = "Expected $this->code status code, received {$response->getStatusCode()}";
            $exception = new UnexpectedStatusCodeException($msg);
            return $response->withException($exception);
        }

        return $response;
    }
}
