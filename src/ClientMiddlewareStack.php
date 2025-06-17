<?php

namespace PainlessPHP\Http\Client;

use GuzzleHttp\Promise\Promise;
use PainlessPHP\Http\Client\Contract\ClientMiddleware;
use PainlessPHP\Http\Client\Contract\ClientRequestProcessor;
use PainlessPHP\Http\Client\Internal\ClosureClientRequestProcessor;
use PainlessPHP\Http\Message\Concern\CreatableFromArray;

class ClientMiddlewareStack
{
    use CreatableFromArray;

    /**
     * @var array<ClientMiddleware> $middlewares
     */
    private array $middlewares;

    /**
     * @param array<ClientMiddleware> $middlewares
     */
    public function __construct(array $middlewares)
    {
        $this->setMiddlewares(...$middlewares);
    }

    /**
     * Acts as a type checker
     *
     */
    private function setMiddlewares(ClientMiddleware ...$middlewares)
    {
        $this->middlewares = $middlewares;
    }

    /**
     * Apply the middleware stack to a given response
     *
     */
    public function apply(ClientRequest $request, ClientRequestProcessor $handler) : ClientResponse
    {
        foreach(array_reverse($this->middlewares) as $middleware) {
            $handler = $middleware->toClientRequestProcessor($handler);
        }
        return $handler->process($request);
    }

    public function withAdditionalMiddleware(ClientMiddleware $middleware) : self
    {
        return new self([...$this->middlewares, $middleware]);
    }

    public function withAdditionalMiddlewares(ClientMiddleware ...$middlewares) : self
    {
        return new self([...$this->middlewares, ...$middlewares]);
    }

    public function toArray() : array
    {
        return $this->middlewares;
    }
}
