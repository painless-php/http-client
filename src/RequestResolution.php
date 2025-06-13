<?php

namespace PainlessPHP\Http\Client;

use PainlessPHP\Http\Client\Exception\MessageException;

class RequestResolution
{
    public function __construct(
        private ClientRequest $request,
        private ClientResponse|MessageException $resolution
    ) {
    }

    public function getRequest() : ClientRequest
    {
        return $this->request;
    }

    public function getResolution() : ClientResponse|MessageException
    {
        return $this->resolution;
    }
}
