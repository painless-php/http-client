<?php

namespace PainlessPHP\Http\Client\Concern;

use PainlessPHP\Http\Client\ClientRequest;
use PainlessPHP\Http\Client\ClientResponse;
use PainlessPHP\Http\Client\Contract\ClientRequestProcessor;
use Wor\Skeleton\Middleware\ClosureClientRequestProcessor;

trait IntoClientRequestProcessor
{
    /**
     * This is basically inherited from ClientMiddleware
     */
    abstract public function apply(ClientRequest $request, ClientRequestProcessor $next) : ClientResponse;

    /**
     * Implement
     */
    public function toClientRequestProcessor(ClientRequestProcessor $last) : ClientRequestProcessor
    {
        return new ClosureClientRequestProcessor(function(ClientRequest $request) use($last) {
            return $this->apply($request, $last);
        });
    }
}
