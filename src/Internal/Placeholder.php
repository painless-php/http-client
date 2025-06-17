<?php

namespace PainlessPHP\Http\Client\Internal;

class Placeholder
{
    public static function replaceAll(string $string, array $placeholders) : string
    {
        foreach ($placeholders as $name => $value) {
            $string = str_replace('{' . $name . '}', $value, $string);
        }
        var_dump($string);
        return $string;
    }
}
