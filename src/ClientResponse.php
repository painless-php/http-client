<?php

namespace PainlessPHP\Http\Client;

use PainlessPHP\Http\Client\Exception\ResponseException;
use PainlessPHP\Http\Message\HeaderCollection;
use PainlessPHP\Http\Message\Response;
use PainlessPHP\Http\Message\Status;

/**
 * Response class that adds additional response handling functionality to the
 * basic psr-7 compliant response interface
 *
 */
class ClientResponse extends Response
{
    protected array $exceptions;
    protected array $redirections;

    public function __construct(
        private ClientRequest $request,
        Status|int $status = 200,
        mixed $body = null,
        HeaderCollection|array $headers = [],
        array $redirections = []
    ) {
        parent::__construct($status, $body, $headers);
        $this->request = $request;
        $this->exceptions = [];
        $this->setRedirections(...$redirections);
    }

    private function setRedirections(Redirection ...$redirections)
    {
        $this->redirections = $redirections;
    }

    /**
     * Get the final URI connected to after all redirections
     *
     */
    public function getEffectiveUri() : string
    {
        if(empty($this->redirections)) {
            return $this->request->getUri();
        }

        /* Return last redirection location */
        return (string)$this->redirections[count($this->redirections) -1]->getDestination();
    }

    /**
     * Create a new instance with the given exception
     *
     */
    public function withException(ResponseException $exception) : self
    {
        $instance = $this->clone();
        $instance->exceptions[] = $exception;
        return $instance;
    }

    /**
     * @return array<ResponseException>
     *
     */
    public function getExceptions() : array
    {
        return $this->exceptions;
    }

    /**
     * Get all exception messages for this response
     *
     */
    public function getExceptionMessages() : array
    {
        return array_map(function($exception) {
            return $exception->getMessage();
        }, $this->exceptions);
    }

    /**
     * Check if this response has any exceptions
     *
     */
    public function hasExceptions() : bool
    {
        return ! empty($this->exceptions);
    }

    /**
     * Create a new response instance with a given redirection
     *
     */
    public function withRedirection(Redirection $redirection) : self
    {
        $instance = $this->clone();
        $instance->redirections[] = $redirection;
        return $instance;
    }

    /**
     * Get an array containing all redirections
     *
     */
    public function getRedirections() : array
    {
        return $this->redirections;
    }

    /**
     * Get an array containing all redirection destinations
     *
     */
    public function getRedirectionTrace() : array
    {
        return array_map(function($redirection) {
            return $redirection->getDestination();
        }, $this->redirections);
    }

    /**
     * Check if the response was successful
     *
     */
    public function isSuccessful() : bool
    {
        return empty($this->exceptions);
    }

    /**
     * Get the original request that this response belongs to
     *
     */
    public function getRequest() : ClientRequest
    {
        return $this->request;
    }

    /**
     * Throw a response exception if there are any exceptions
     * attached to this response. Does nothing if there are no exceptions
     *
     */
    public function throwExceptions() : void
    {
        if(! $this->hasExceptions()) {
            return;
        }
        $messages = implode(PHP_EOL, $this->getExceptionMessages());
        $msg = 'Response has exceptions: ' . PHP_EOL . $messages;
        throw new ResponseException($msg);
    }
}
