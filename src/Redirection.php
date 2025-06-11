<?php

namespace PainlessPHP\Http\Client;

use PainlessPHP\Http\Message\Status;
use PainlessPHP\Http\Message\StatusCodes;
use PainlessPHP\Http\Message\Uri;
use ValueError;

class Redirection
{
    private $status;

    public function __construct(
        private Uri|string $source,
        private Uri|string $destination,
        Status|int $status
    ) {
        $this->setStatus($status);
    }

    /**
     * Set http status of the redirection. This must be within the 3XX range
     *
     */
    private function setStatus(Status|int $status)
    {
        if(is_int($status)) {
            $status = StatusCodes::getStatusForCode($status);
        }

        $code = $status->getCode();
        if($code < 300 || $code > 399) {
            $msg = "Given status code '$code' is not with the valid 3XX range for redirections";
            throw new ValueError($msg);
        }

        $this->status = $status;
    }

    /**
     * Get the redirection source
     *
     */
    public function getSource() : Uri
    {
        return $this->source;
    }

    /**
     * Get the redirection destination
     *
     */
    public function getDestination() : Uri
    {
        return $this->destination;
    }

    /**
     * Get the redirection status. Code is always from 3XX range
     *
     */
    public function getStatus() : Status
    {
        return $this->status;
    }

    /**
     * Get an array representation
     *
     */
    public function toArray() : array
    {
        return [
            'source'      => $this->source,
            'destination' => $this->destination,
            'status'      => $this->status->toArray()
        ];
    }
}
