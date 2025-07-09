<?php

namespace PainlessPHP\Http\Client\Internal;

class Str
{
    /**
     * Check if the given value can be cast to string
     *
     * @param mixed $value
     *
     */
    public static function isConvertable($value) : bool
    {
        return $value === null ||
            is_scalar($value) ||
            (is_object($value) && method_exists($value, '__toString'));
    }
}
