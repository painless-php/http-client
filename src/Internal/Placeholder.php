<?php

namespace PainlessPHP\Http\Client\Internal;

class Placeholder
{
    public static function replaceAll(string $string, array $placeholders) : string
    {
        foreach ($placeholders as $name => $value) {
            // Make sure that the given placeholder value can be uses by str_replace
            if(Str::isConvertable($value)) {
                $string = str_replace('{' . $name . '}', strval($value), $string);
            }
        }
        return $string;
    }
}
