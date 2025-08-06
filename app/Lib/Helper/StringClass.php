<?php

namespace App\Lib\Helper;

class StringClass
{
    public static function isStringSecure($string)
    {
        $minLength = 1;
        $maxLength = 60;

        // Define a regular expression pattern to allow only alphanumeric characters.
        $pattern = '/^[a-zA-Z0-9]*$/';

        if (is_null($string)) {
            return null;
        }
        // Check if the string length is within the specified bounds.
        if (strlen($string) < $minLength || strlen($string) > $maxLength) {
            return null;
        }

        // Sanitize the string.
        $sanitizedString = filter_var($string, FILTER_SANITIZE_STRING);

        // Validate the string against the pattern.
        if (!preg_match($pattern, $sanitizedString)) {
            return null;
        }

        // If all checks pass, return true.
        return $string;
    }
}
