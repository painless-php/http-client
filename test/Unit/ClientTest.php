<?php

namespace PainlessPHP\Http\Client;

use Mockery;
use PainlessPHP\Http\Client\Adapter\GuzzleClientAdapter;
use PainlessPHP\Http\Client\Middleware\Request\LogRequest;
use PainlessPHP\Http\Client\Middleware\Response\LogResponse;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ClientTest extends TestCase
{
    public function test()
    {
        $mock = Mockery::spy(LoggerInterface::class);
        $requestFormat = "Client {attribute:name}: sending {request:method} request to {request:uri}";
        $responseFormat = "Client {attribute:name}: received {status:code} response";

        $client = new Client(
            adapter: new GuzzleClientAdapter,
            requestMiddlewares: [new LogRequest(logger: $mock, format: $requestFormat)],
            responseMiddlewares: [new LogResponse(logger: $mock, format: $responseFormat)]
        );

        $responses = $client->sendRequests([
            $client->createRequest('GET', 'https://google.com?param=foo')->withAttributes(['name' => 'foo']),
            $client->createRequest('GET', 'https://google.com?param=bar')->withAttributes(['name' => 'bar']),
        ]);

        $this->assertCount(2, $responses->toArray());
    }
}
