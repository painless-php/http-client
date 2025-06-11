<?php

namespace PainlessPHP\Http\Client\Contract;

use PainlessPHP\Http\Client\ClientRequest;

interface RequestMiddleware
{
    public function apply(ClientRequest $response) : ClientRequest;
}
