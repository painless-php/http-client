<?php

namespace PainlessPHP\Http\Client;

use PainlessPHP\Http\Message\Concern\CreatableFromArray;
use ValueError;

class RequestSettings
{
    use CreatableFromArray;

    private int $timeout;
    private int $maxRedirections;

    public function __construct(
        int $timeout = 10,
        int $maxRedirections = 3
    ) {
        $this->setTimeout($timeout);
        $this->setMaxRedirections($maxRedirections);
    }

    /**
     * Set timeout
     *
     */
    private function setTimeout(float $timeout)
    {
        if($timeout < 0) {
            $msg = "timeout can't be negative";
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
            $msg = "max_redirections can't be negative";
            throw new ValueError($msg);
        }

        $this->maxRedirections = $max;
    }

    public function getTimeout() : int
    {
        return $this->timeout;
    }

    public function getMaxRedirections() : int
    {
        return $this->maxRedirections;
    }
}
