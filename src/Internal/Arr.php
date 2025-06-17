<?php

namespace PainlessPHP\Http\Client\Internal;

class Arr
{
    public static function mapWithKeys(array $array, callable $callback) : array
    {
        $result = [];
        foreach($array as $key => $value) {
            [$key, $value] = $callback($key, $value);
            $result[$key] = $value;
        }
        return $result;
    }
}
