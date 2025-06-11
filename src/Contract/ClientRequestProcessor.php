<?php

namespace PainlessPHP\Http\Client\Contract;

use PainlessPHP\Http\Client\ClientRequest;
use PainlessPHP\Http\Client\ClientResponse;

interface ClientRequestProcessor
{
    public function process(ClientRequest $request) : ClientResponse;
}
