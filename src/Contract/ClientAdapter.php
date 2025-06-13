<?php

namespace PainlessPHP\Http\Client\Contract;

use PainlessPHP\Http\Client\ClientRequest;
use PainlessPHP\Http\Client\ClientResponse;
use PainlessPHP\Http\Client\Exception\ClientException;
use PainlessPHP\Http\Client\Exception\NetworkException;
use PainlessPHP\Http\Client\Exception\RequestException;

interface ClientAdapter
{
    /**
     * Send a single request
     *
     * @throws ClientException
     * @throws RequestException
     * @throws NetworkException
     *
     */
    public function sendRequest(ClientRequest $request) : ClientResponse;

    /**
     * Send multiple requests in parallel
     *
     * @param array<ClientRequest> $requests
     * @param callable(ClientRequest) : ClientRequest $beforeRequest
     * @param callable(ClientResponse) : ClientResponse $afterResponse
     *
     * @return array<ClientResponse>
     *
     */
    public function sendRequests(
        array $requests,
        ?callable $beforeRequest = null,
        ?callable $afterResponse = null
    ) : array;
}
