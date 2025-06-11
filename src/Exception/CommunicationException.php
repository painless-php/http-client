<?php

namespace PainlessPHP\Http\Client\Exception;

use PainlessPHP\Http\Client\ClientRequest;
use Throwable;

/**
 * A type of exception that is thrown when there was some issue
 * with the http communication.
 *
 */
abstract class CommunicationException extends MessageException
{
    public function __construct(
        private ClientRequest $request,
        string $msg = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($msg, $code, $previous);
    }

    public function getRequest(): ClientRequest
    {
        return $this->request;
    }
}
