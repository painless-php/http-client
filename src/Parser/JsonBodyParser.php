<?php

namespace PainlessPHP\Http\Client\Parser;

use PainlessPHP\Http\Client\Contract\BodyParser;
use PainlessPHP\Http\Client\Exception\BodyParsingException;
use PainlessPHP\Http\Message\Body;

class JsonBodyParser implements BodyParser
{
    public function __construct(private bool $decodeToArray = true)
    {

    }

    public function parseBody(Body $body) : mixed
    {
        $content = $body->getContents();

        if(empty($content)) {
            $msg = 'Body content is empty';
            throw new BodyParsingException($msg);
        }

        $parsed = json_decode($content, $this->decodeToArray);

        if($parsed === null) {
            $msg = json_last_error_msg();
            throw new BodyParsingException($msg);
        }

        return $parsed;
    }
}
