<?php

namespace PainlessPHP\Http\Client\Contract;

use PainlessPHP\Http\Client\ClientRequest;
use PainlessPHP\Http\Client\ClientResponse;

interface ClientMiddleware
{
    public function apply(ClientRequest $request, ClientRequestProcessor $next) : ClientResponse;
}
