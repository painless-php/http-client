<?php

namespace PainlessPHP\Http\Client\Parser;

use PainlessPHP\Http\Client\Contract\BodyParser;
use PainlessPHP\Http\Client\Exception\BodyParsingException;
use PainlessPHP\Http\Message\Body;
use PainlessPHP\Http\Message\Exception\StringParsingException;
use PainlessPHP\Http\Message\Query;

class FormBodyParser implements BodyParser
{
    public function parseResponseBody(Body $body) : array
    {
        $content = $body->getContents();

        try {
            $query = Query::createFromQueryString($content);
            return $query->toArray();
        }
        catch(StringParsingException $e) {
            $msg = "Malformed form body '$content'";
            throw new BodyParsingException($msg, 0, $e);
        }
    }
}
