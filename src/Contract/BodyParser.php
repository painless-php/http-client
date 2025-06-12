<?php

namespace PainlessPHP\Http\Client\Contract;

use PainlessPHP\Http\Message\Body;
use PainlessPHP\Http\Client\Exception\BodyParsingException;

interface BodyParser
{
    /**
     * @throws BodyParsingException
     *
     */
     function parseResponseBody(Body $body);
}
