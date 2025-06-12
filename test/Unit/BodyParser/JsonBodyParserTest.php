<?php

namespace Test\Unit\BodyParser;

use PainlessPHP\Http\Client\Exception\BodyParsingException;
use PainlessPHP\Http\Client\Parser\JsonBodyParser;
use PainlessPHP\Http\Message\Body;
use PHPUnit\Framework\TestCase;

class JsonBodyParserTest extends TestCase
{
    public function testParseBodyParsesJson()
    {
        $parser = new JsonBodyParser();
        $data = ['foo', 'bar', 'baz'];
        $body = new Body(json_encode($data));
        $result = $parser->parseBody($body);
        $this->assertSame($data, $result);
    }

    public function testParseBodyThrowsExceptionForInvalidJson()
    {
        $parser = new JsonBodyParser();
        $body = new Body('{sfasfaj');
        $this->expectException(BodyParsingException::class);
        $parser->parseBody($body);
    }
}
