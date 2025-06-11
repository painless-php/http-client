<?php

namespace PainlessPHP\Http\Client\Exception;

use Psr\Http\Client\ClientExceptionInterface;

/**
 *
 * A psr 18 compatible exception that will be throw
 * ONLY if the request could not be sent at all or
 * the response could not be parsed into a psr-7 compatible object.
 *
 */
class ClientException extends MessageException implements ClientExceptionInterface
{

}
