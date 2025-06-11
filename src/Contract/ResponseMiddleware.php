<?php

namespace PainlessPHP\Http\Client\Contract;

use PainlessPHP\Http\Client\ClientResponse;

interface ResponseMiddleware
{
    public function apply(ClientResponse $response) : ClientResponse;
}
