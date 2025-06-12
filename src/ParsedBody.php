<?php

namespace PainlessPHP\Http\Client;

use PainlessPHP\Http\Message\Body;

/**
 * A wrapper for a message body with content that has been parsed already.
 *
 */
class ParsedBody extends Body
{
    protected mixed $parsedContent;

    public function __construct(Body $original, mixed $parsedContent)
    {
        parent::__construct($original);
        $this->parsedContent = $parsedContent;
    }

    public function getParsedContent() : mixed
    {
        return $this->parsedContent;
    }
}
