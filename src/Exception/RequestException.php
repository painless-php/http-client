<?php

namespace PainlessPHP\Http\Client\Exception;

use Psr\Http\Client\RequestExceptionInterface;

/**
 * A psr-18 compatible exception that will be thrown when
 * the request could not be sent because the request message is not well-formed
 * or is missing some critical piece of information.
 *
 */
class RequestException extends CommunicationException implements RequestExceptionInterface
{
}
