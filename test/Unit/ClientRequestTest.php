<?php

namespace Test\Unit;

use Mockery;
use PainlessPHP\Http\Client\ClientRequest;
use PainlessPHP\Http\Client\Middleware\Response\LogResponse;
use PainlessPHP\Http\Client\Middleware\Response\ParseResponseBody;
use PainlessPHP\Http\Client\RequestSettings;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;

class ClientRequestTest extends TestCase
{
    private $request;

    public function setUp() : void
    {
        parent::setUp();
        $this->request = new ClientRequest('GET', 'https://google.com');
    }

    public function testImplementsRequestInterface()
    {
        $this->assertInstanceOf(RequestInterface::class, $this->request);
    }

    public function testWithSettingsModifiesRequestSettings()
    {
        $request = $this->request->withSettings(new RequestSettings(timeout: 15));
        $this->assertSame(15, $request->getSettings()->getTimeout());
    }

    public function testWithSettingsDoesNotModifyOriginalRequest()
    {
        $this->request->withSettings(new RequestSettings(timeout: 15));
        $this->assertSame(10, $this->request->getSettings()->getTimeout());
    }

    public function testWithSettingsAcceptsArrayAsSettings()
    {
        $request = $this->request->withSettings(['timeout' => 15]);
        $this->assertSame(15, $request->getSettings()->getTimeout());
    }

    public function testWithAdditionalResponseMiddlewareAddsTheGivenMiddleware()
    {
        $middleware = new ParseResponseBody;
        $request = $this->request->withAdditionalResponseMiddleware($middleware);
        $this->assertSame([$middleware], $request->getResponseMiddlewareStack()->toArray());
    }

    public function testWithAdditionalResponseMiddlewareDoesNotModifyTheOriginalRequest()
    {
        $middleware = new ParseResponseBody;
        $this->request->withAdditionalResponseMiddleware($middleware);
        $this->assertEmpty($this->request->getResponseMiddlewareStack()->toArray());
    }

    public function testWithResponseMiddlewaresAddsGivenMiddlewares()
    {
        $middlewares = [
            new ParseResponseBody,
            new LogResponse(Mockery::spy(LoggerInterface::class))
        ];
        $request = $this->request->withResponseMiddlewares($middlewares);
        $this->assertSame($middlewares, $request->getResponseMiddlewareStack()->toArray());
    }

    public function testWithResponseMiddlewaresDoesNotModifyOriginalRequest()
    {
        $middlewares = [
            new ParseResponseBody,
            new LogResponse(Mockery::spy(LoggerInterface::class))
        ];
        $this->request->withResponseMiddlewares($middlewares);
        $this->assertEmpty($this->request->getResponseMiddlewareStack()->toArray());
    }
}
