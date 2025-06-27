<?php

namespace PainlessPHP\Http\Client;

use PainlessPHP\Http\Message\Body;

/**
 * A wrapper for a message body with content that has been parsed already.
 *
 */
class ParsedBody extends Body
{
    public function __construct(
        Body $original,
        protected mixed $parsed
    )
    {
        parent::__construct($original);
    }

    public function getParsedContent() : mixed
    {
        return $this->parsed;
    }
}
