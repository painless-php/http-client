<?php

namespace PainlessPHP\Http\Client\Internal;

class Arr
{
    public static function pathExists(array $array, string $path, string $pathSeparator = '.')
    {
        $parts = explode($pathSeparator, $path);
        $current = array_splice($parts, 0, 1)[0];

        if(! array_key_exists($current, $array)) {
            return false;
        }

        if(empty($parts)) {
            return true;
        }

        return self::pathExists(
            $array[$current],
            implode($pathSeparator, $parts),
            $pathSeparator
        );
    }

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
