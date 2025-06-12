<?php

namespace PainlessPHP\Http\Client;

use PainlessPHP\Http\Message\Concern\CreatableFromArray;
use ValueError;

class RequestSettings
{
    use CreatableFromArray;

    private int|float $timeout;
    private int $maxRedirections;

    public function __construct(
        int|float $timeout = 10,
        int $maxRedirections = 3
    ) {
        $this->setTimeout($timeout);
        $this->setMaxRedirections($maxRedirections);
    }

    /**
     * Set timeout
     *
     */
    private function setTimeout(int|float $timeout)
    {
        if($timeout < 0) {
            $msg = "Request timeout can't be negative";
            throw new ValueError($msg);
        }

        $this->timeout = $timeout;
    }

    /**
     * Set max redirections
     *
     */
    private function setMaxRedirections(int $max)
    {
        if($max < 0) {
            $msg = "Request max_redirections can't be negative";
            throw new ValueError($msg);
        }

        $this->maxRedirections = $max;
    }

    public function getTimeout() : int|float
    {
        return $this->timeout;
    }

    public function getMaxRedirections() : int
    {
        return $this->maxRedirections;
    }
}
