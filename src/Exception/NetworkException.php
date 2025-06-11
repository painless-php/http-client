<?php

namespace PainlessPHP\Http\Client\Exception;

use Psr\Http\Client\NetworkExceptionInterface;

/**
 * A psr-18 compatible exception that will be thrown when
 * the request could not be sent due to network issues, including timeout.
 *
 */
class NetworkException extends CommunicationException implements NetworkExceptionInterface
{
}
