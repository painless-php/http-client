<?php

namespace PainlessPHP\Http\Client\Internal;

use Closure;
use PainlessPHP\Http\Client\ClientRequest;
use PainlessPHP\Http\Client\ClientResponse;
use PainlessPHP\Http\Client\Contract\ClientRequestProcessor;

class ClosureClientRequestProcessor implements ClientRequestProcessor
{
    public function __construct(private Closure $closure)
    {
    }

    public function process(ClientRequest $request) : ClientResponse
    {
        return ($this->closure)($request);
    }
}
