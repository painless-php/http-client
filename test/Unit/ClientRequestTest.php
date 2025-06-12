<?php

namespace Test\Unit;

use PainlessPHP\Http\Client\ClientRequest;
use PainlessPHP\Http\Client\Middleware\Response\ParseResponseBody;
use PainlessPHP\Http\Client\RequestSettings;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

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

    public function testWithResponseMiddlewareAddsTheGivenMiddleware()
    {
        $middleware = new ParseResponseBody;
        $request = $this->request->withResponseMiddleware($middleware);
        $this->assertSame([$middleware], $request->getResponseMiddlewareStack()->toArray());
    }

    public function testWithResponseMiddlewareDoesNotModifyTheOriginalRequest()
    {
        $middleware = new ParseResponseBody;
        $this->request->withResponseMiddleware($middleware);
        $this->assertEmpty($this->request->getResponseMiddlewareStack()->toArray());
    }
}
