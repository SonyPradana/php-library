<?php

declare(strict_types=1);

namespace System\Text;

class Str
{
    /**
     * Return the character at the specifid postion.
     *
     * @param string $text String text
     * @param int $index character position
     * @return string|false
     */
    public static function chartAt(string $text, int $index)
    {
        return substr($text, $index, 1);
    }

    /**
     * Join two or more string into once.
     *
     * @param array<int, string> $text String array
     * @param string $sparator Sparator
     * @param string $sparator Sparator before last item
     * @return string
     */
    public static function concat(array $text, string $sparator = ' ', string $last_separator = '')
    {
        if ('' !== $last_separator) {
            $remove_last = array_pop($text);
            $text[] = $last_separator;
            $text[] = $remove_last;
        }

        return implode($sparator, $text);
    }

    /**
     * Index of first occorrent of specified text with in string.
     *
     * @param string $text String to search
     * @param string $find Find
     * @return int|false
     */
    public static function indexOf(string $text, string $find)
    {
        return strpos($text, $find, 1);
    }

    /**
     * Last index of first occorrent of specified text with in string.
     *
     * @param string $text String to search
     * @param string $find Find
     * @return int|false
     */
    public static function lasrIndexOf(string $text, string $find)
    {
        return strpos($text, $find, -1);
    }

    /**
     * Retreves the matches of string against a search pattern.
     *
     * @param string $text String
     * @param string $pattern String leguler expresstion
     * @return array|null Null if not match found
     */
    public static function match(string $text, string $pattern)
    {
        $matches = [];
        $has_result = preg_match($pattern, $text, $matches);

        if (1 === $has_result) {
            return $matches;
        }

        return null;
    }

    /**
     * Find and replace specified text in string.
     *
     * @param string $original The subject text
     * @param string|array $find find
     * @param string|array $replace replace
     */
    public static function replace(string $original, $find, $replace)
    {
        return str_replace($find, $replace, $original);
    }

    /**
     * Search for matching text and return as position.
     *
     * @param string $text String to search
     * @param string $find Find
     * @return int|false
     */
    public static function search(string $text, string $find)
    {
        return strpos($text, $find);
    }

    /**
     * Extracts a section of string.
     *
     * @param string $text String to slice
     * @param int $start Start position text
     * @param int|null $length Length of string
     * @return string|false
     */
    public static function slice(string $text, int $start, ?int $length = null)
    {
        return substr($text, $start, $length);
    }

    /**
     * Splits a string into array of string.
     *
     * @param string $text String to split.
     * @param string $sparator Sparator
     * @param int|null $limit Limit array length
     * @return array<int, string>|false
     */
    public static function split(string $text, string $sparator = "", int $limit = PHP_INT_MAX)
    {
        return explode($sparator, $text, $limit);
    }

    /**
     * Convert string to lowercase.
     *
     * @param string $text Input string
     * @return string
     */
    public static function toLowerCase(string $text)
    {
        return strtolower($text);
    }

    /**
     * Convert string to lowercase.
     *
     * @param string $text Input string
     * @return string
     */
    public static function toUpperCase(string $text)
    {
        return strtoupper($text);
    }

    // additional ------------------------------

    /**
     * Make frist charater uppercase.
     *
     * @param string $text Input string
     * @return string
     */
    public static function firstUpper(string $text)
    {
        return ucfirst($text);
    }

    /**
     * Make frist charater uppercase each words.
     *
     * @param string $text Input string
     * @return string
     */
    public static function firstUpperAll(string $text)
    {
        return ucwords($text);
    }

    /**
     * Make text sparate with dash (snackcase).
     *
     * @param string $text input text
     * @return string
     */
    public static function toSnackCase(string $text)
    {
        return str_replace([' ', '-', '_', '+'], '_', $text);
    }

    /**
     * Make text sparate with - (kebabcase).
     *
     * @param string $text input text
     * @return string
     */
    public static function toKebabCase(string $text)
    {
        return str_replace([' ', '-', '_', '+'], '-', $text);
    }

    /**
     * Make text each word start with capital (pascalcase).
     *
     * @param string $text input text
     * @return string
     */
    public static function toPascalCase(string $text)
    {
        $space_case  = str_replace(['-', '_', '+'], ' ', $text);
        $first_upper = static::firstUpperAll($space_case);

        return str_replace(' ', '', $first_upper);
    }

    /**
     * Make text camelcase.
     *
     * @param string $text input text
     * @return string
     */
    public static function toCamelCase(string $text)
    {
        $space_case  = str_replace(['-', '_', '+'], ' ', $text);
        $arr_text = explode(' ', $space_case);
        $result = [];
        $first_text = true;

        foreach ($arr_text as $text) {
            if ($first_text) {
                $result[] = strtolower($text);
                $first_text = false;
                continue;
            }

            $result[] = ucfirst($text);
        }

        return implode('', $result);
    }
}