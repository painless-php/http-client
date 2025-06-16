<?php

namespace PainlessPHP\Http\Client;

use Mockery;
use PainlessPHP\Http\Client\Adapter\GuzzleClientAdapter;
use PainlessPHP\Http\Client\Middleware\Client\LogTraffic;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class ClientTest extends TestCase
{
    public function test()
    {
        $mock = Mockery::spy(LoggerInterface::class);

        $client = new Client(
            adapter: new GuzzleClientAdapter,
            middlewares: [
                new LogTraffic($mock, LogLevel::INFO)
            ]
        );

        // $response = $client->request('GET', 'https://google.com');
        // dd($response->getStatus());

        $responses = $client->sendRequests([
            $client->createRequest('GET', 'https://google.com?param=foo'),
            $client->createRequest('GET', 'https://google.com?param=bar'),
        ]);

        // dd($responses->hasExceptions());
    }
}
