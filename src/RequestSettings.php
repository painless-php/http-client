<?php

namespace PainlessPHP\Http\Client;

use PainlessPHP\Http\Client\Internal\Arr;
use PainlessPHP\Http\Message\Concern\CreatableFromArray;
use ReflectionMethod;
use ValueError;

class RequestSettings
{
    use CreatableFromArray;

    private const array DEFAULTS = [
        'timeout' => 10,
        'maxRedirections' => 3
    ];

    private int|float $timeout;
    private int $maxRedirections;
    private array $defined;

    public function __construct(
        int|float|null $timeout = null,
        int|null $maxRedirections = null
    ) {
        $values = func_get_args();
        $names = array_map(fn($param) => $param->getName(), new ReflectionMethod($this, '__construct')->getParameters());
        $result = [];

        foreach($names as $position => $name) {
            if(array_key_exists($position, $values)) {
                $result[$name] = $values[$position];
            }
        }
        $this->initialize($result);
    }

    private function initialize(array $values)
    {
        // Save keys that were specified by user
        $values = array_filter($values, fn($value) => $value !== null);
        $this->defined = array_keys($values);

        // Create setting values while filling in defaults
        $settings = array_merge(self::DEFAULTS, $values);

        // Call setting setters dynamically
        foreach($settings as $name => $value) {
            $setter = "set" . ucfirst($name);
            $this->$setter($value);
        }
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

    /**
     * Get an array containing all settings that were
     * defined explictly when the settings object was created
     *
     */
    public function getExplicit() : array
    {
        return array_intersect_key(
            $this->toArray(),
            array_flip($this->defined)
        );
    }

    public function toArray() : array
    {
        return Arr::mapWithKeys(
            self::DEFAULTS,
            fn($key) => [$key, $this->$key]
        );
    }
}
