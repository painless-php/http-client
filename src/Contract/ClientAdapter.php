<?php

namespace PainlessPHP\Http\Client\Contract;

use PainlessPHP\Http\Client\ClientMiddlewareStack;
use PainlessPHP\Http\Client\ClientRequest;
use PainlessPHP\Http\Client\ClientResponse;
use PainlessPHP\Http\Client\Exception\ClientException;
use PainlessPHP\Http\Client\Exception\NetworkException;
use PainlessPHP\Http\Client\Exception\RequestException;
use PainlessPHP\Http\Client\RequestResolutionCollection;

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
     * @return RequestResolutionCollection
     *
     */
    public function sendRequests(
        array $requests,
        ClientMiddlewareStack $middlewares,
        ?callable $beforeRequest = null,
        ?callable $afterResponse = null,
        ?int $concurrency = null
    ) : RequestResolutionCollection;
}
