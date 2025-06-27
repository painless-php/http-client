<?php

namespace Test\Unit\Middleware\Response;

use Mockery;
use PainlessPHP\Http\Client\ClientRequest;
use PainlessPHP\Http\Client\ClientResponse;
use PainlessPHP\Http\Client\Middleware\Response\ResponseDataContains;
use PainlessPHP\Http\Client\ParsedBody;
use PainlessPHP\Http\Message\Body;
use PHPUnit\Framework\TestCase;

class ResponseDataContainsTest extends TestCase
{
    private function createResponse(array $data)
    {
        $body = new ParsedBody(
            original: new Body(json_encode($data)),
            parsed: $data
        );

        $request = Mockery::spy(ClientRequest::class);
        return new ClientResponse(
            request: $request,
            body: $body
        );
    }

    public function testEmptyBodyWillAddExceptionWhenOneKeyIsExpected()
    {
        $middleware = new ResponseDataContains('foo');
        $response = $middleware->apply($this->createResponse([]));

        $this->assertCount(1, $response->getExceptions());
    }

    public function testEmptyBodyWillAddMultipleExceptionsWhenMultipleKeysAreExpected()
    {
        $middleware = new ResponseDataContains(['foo', 'bar', 'baz']);
        $response = $middleware->apply($this->createResponse([]));

        $this->assertCount(3, $response->getExceptions());

    }

    public function testStringConstructorCanPassWithSingleValue()
    {
        $middleware = new ResponseDataContains('foo');
        $response = $middleware->apply($this->createResponse(['foo' => 1]));

        $this->assertCount(0, $response->getExceptions());
    }

    public function testStringConstructorCanFailWithSingleValue()
    {
        $middleware = new ResponseDataContains('bar');
        $response = $middleware->apply($this->createResponse(['foo' => 1]));

        $this->assertCount(1, $response->getExceptions());
    }

    public function testNoExceptionsAreAddedWhenAllKeysAreDefined()
    {
        $middleware = new ResponseDataContains(['foo', 'bar', 'baz']);
        $response = $middleware->apply($this->createResponse(['foo' => 1, 'bar' => 2, 'baz' => 3]));

        $this->assertCount(0, $response->getExceptions());
    }

    public function testExceptionsAreAddedForEachMissingKey()
    {
        $middleware = new ResponseDataContains(['foo', 'bar', 'baz']);
        $response = $middleware->apply($this->createResponse(['foo' => 1, 'baz' => 2]));

        $this->assertCount(1, $response->getExceptions());
    }

    public function testNestedValuesCanBeCheckedAndPass()
    {
        $middleware = new ResponseDataContains('foo.bar.baz');
        $response = $middleware->apply($this->createResponse(['foo' => ['bar' => ['baz' => 1]]]));

        $this->assertCount(0, $response->getExceptions());
    }

    public function testNestedValuesCanBeCheckedAndFail()
    {
        $middleware = new ResponseDataContains('foo.bar.bazzz');
        $response = $middleware->apply($this->createResponse(['foo' => ['bar' => ['baz' => 1]]]));

        $this->assertCount(1, $response->getExceptions());
    }
}
