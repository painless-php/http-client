<?php

namespace PainlessPHP\Http\Client;

use PainlessPHP\Http\Client\Contract\ResponseMiddleware;
use PainlessPHP\Http\Message\Concern\CreatableFromArray;

class ResponseMiddlewareStack
{
    use CreatableFromArray;

    private array $middlewares;

    /**
     * @param array<ResponseMiddleware> $middlewares
     */
    public function __construct(array $middlewares)
    {
        $this->setMiddlewares(...$middlewares);
    }

    /**
     * Acts as a type checker
     *
     */
    private function setMiddlewares(ResponseMiddleware ...$middlewares)
    {
        $this->middlewares = $middlewares;
    }

    /**
     * Apply the middleware stack to a given response
     *
     */
    public function apply(ClientResponse $response) : ClientResponse
    {
        foreach($this->middlewares as $middleware) {
            $response = $middleware->apply($response);
        }
        return $response;
    }

    public function withAdditionalMiddleware(ResponseMiddleware $middleware) : self
    {
        return new self([...$this->middlewares, $middleware]);
    }

    public function withAdditionalMiddlewares(ResponseMiddleware ...$middlewares) : self
    {
        return new self([...$this->middlewares, ...$middlewares]);
    }
}
