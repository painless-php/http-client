<?php

namespace PainlessPHP\Http\Client\Parser;

use PainlessPHP\Http\Client\Contract\BodyParser;
use PainlessPHP\Http\Client\Exception\BodyParsingException;
use PainlessPHP\Http\Message\Body;
use stdClass;

class JsonBodyParser implements BodyParser
{
    public function __construct(private bool $decodeToArray = true)
    {

    }

    public function parseResponseBody(Body $body) : stdClass|array
    {
        $parsed = json_decode($body->getContents(), $this->decodeToArray);

        if($parsed === null) {
            $msg = json_last_error_msg();
            throw new BodyParsingException($msg);
        }

        return $parsed;
    }
}
