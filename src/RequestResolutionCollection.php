<?php

namespace PainlessPHP\Http\Client;

class RequestResolutionCollection
{
    private array $resolutions;

    public function __construct(array $resolutions)
    {
        $this->setResolutions(...$resolutions);
    }

    private function setResolutions(RequestResolution ...$resolutions)
    {
        $this->resolutions = $resolutions;
    }

    public function hasExceptions() : bool
    {
        foreach($this->resolutions as $resolution) {
            if($resolution->failed()) {
                return true;
            }
        }
        return false;
    }

    public function toArray() : array
    {
        return $this->resolutions;
    }
}
