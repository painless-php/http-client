<?php

namespace PainlessPHP\Http\Client;

use PainlessPHP\Http\Client\Contract\RequestMiddleware;
use PainlessPHP\Http\Message\Concern\CreatableFromArray;

class RequestMiddlewareStack
{
    use CreatableFromArray;

    private array $middlewares;

    /**
     * @param array<RequestMiddleware> $middlewares
     */
    public function __construct(array $middlewares)
    {
        $this->setMiddlewares(...$middlewares);
    }

    /**
     * Acts as a type checker
     *
     */
    private function setMiddlewares(RequestMiddleware ...$middlewares)
    {
        $this->middlewares = $middlewares;
    }

    /**
     * Apply the middleware stack to a given response
     *
     */
    public function apply(ClientRequest $request) : ClientRequest
    {
        foreach($this->middlewares as $middleware) {
            $request = $middleware->apply($request);
        }
        return $request;
    }

    public function withAdditionalMiddleware(RequestMiddleware $middleware) : self
    {
        return new self([...$this->middlewares, $middleware]);
    }

    public function withAdditionalMiddlewares(RequestMiddleware ...$middlewares) : self
    {
        return new self([...$this->middlewares, ...$middlewares]);
    }

    public function toArray() : array
    {
        return $this->middlewares;
    }
}
