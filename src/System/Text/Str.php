<?php

declare(strict_types=1);

namespace System\Text;

use System\Support\Marco;
use System\Text\Exceptions\NoReturn;

final class Str
{
    use Marco;

    /**
     * Create new instace.
     *
     * @param string $text Input text
     *
     * @return Text
     */
    public static function of(string $text)
    {
        return new Text($text);
    }

    /**
     * Return the character at the specifid postion.
     *
     * @param string $text  String text
     * @param int    $index character position
     *
     * @return string|false
     */
    public static function chartAt(string $text, int $index)
    {
        return mb_substr($text, $index, 1);
    }

    /**
     * Join two or more string into once.
     *
     * @param array<int, string> $text     String array
     * @param string             $sparator Sparator
     * @param string             $sparator Sparator before last item
     *
     * @return string
     */
    public static function concat(array $text, string $sparator = ' ', string $last_separator = '')
    {
        if ('' !== $last_separator) {
            $remove_last = array_pop($text);
            $text[]      = $last_separator;
            $text[]      = $remove_last;
        }

        return implode($sparator, $text);
    }

    /**
     * Index of first occorrent of specified text with in string.
     *
     * @param string $text String to search
     * @param string $find Find
     *
     * @return int|false
     */
    public static function indexOf(string $text, string $find)
    {
        return mb_strpos($text, $find, 1);
    }

    /**
     * Last index of first occorrent of specified text with in string.
     *
     * @param string $text String to search
     * @param string $find Find
     *
     * @return int|false
     */
    public static function lastIndexOf(string $text, string $find)
    {
        return mb_strpos($text, $find, -1);
    }

    /**
     * Retreves the matches of string against a search pattern.
     *
     * @param string $text    String
     * @param string $pattern String leguler expresstion
     *
     * @return array<int, string>|null Null if not match found
     */
    public static function match(string $text, string $pattern)
    {
        $matches    = [];
        $has_result = \preg_match($pattern, $text, $matches);

        if (1 === $has_result) {
            return $matches;
        }

        return null;
    }

    /**
     * Find and replace specified text in string.
     *
     * @param string                    $original The subject text
     * @param string|array<int, string> $find     find
     * @param string|array<int, string> $replace  replace
     *
     * @return string
     */
    public static function replace(string $original, $find, $replace)
    {
        return \str_replace($find, $replace, $original);
    }

    /**
     * Search for matching text and return as position.
     *
     * @param string $text String to search
     * @param string $find Find
     *
     * @return int|false
     */
    public static function search(string $text, string $find)
    {
        return mb_strpos($text, $find);
    }

    /**
     * Extracts a section of string.
     *
     * @param string   $text   String to slice
     * @param int      $start  Start position text
     * @param int|null $length Length of string
     *
     * @return string|false
     */
    public static function slice(string $text, int $start, ?int $length)
    {
        $text_length = $length ?? self::length($text);

        return mb_substr($text, $start, $text_length);
    }

    /**
     * Splits a string into array of string.
     *
     * @param string $text     string to split
     * @param string $sparator Sparator
     * @param int    $limit    Limit array length
     *
     * @return string[]|false
     */
    public static function split(string $text, $sparator = '', $limit = PHP_INT_MAX)
    {
        return '' === $sparator
            ? \preg_split('//', $text, -1, PREG_SPLIT_NO_EMPTY)
            : \explode($sparator, $text, $limit);
    }

    /**
     * Convert string to lowercase.
     *
     * @param string $text Input string
     *
     * @return string
     */
    public static function toLowerCase(string $text)
    {
        return mb_strtolower($text);
    }

    /**
     * Convert string to lowercase.
     *
     * @param string $text Input string
     *
     * @return string
     */
    public static function toUpperCase(string $text)
    {
        return mb_strtoupper($text);
    }

    /**
     * Get string after find text find.
     */
    public static function after(string $text, string $find): string
    {
        $length = strlen($find);
        if (false === ($pos = static::indexOf($text, $find))) {
            return $text;
        }

        return mb_substr($text, $pos + $length);
    }

    // additional ------------------------------

    /**
     * Make frist charater uppercase.
     *
     * @param string $text Input string
     *
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
     *
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
     *
     * @return string
     */
    public static function toSnackCase(string $text)
    {
        return \str_replace([' ', '-', '_', '+'], '_', $text);
    }

    /**
     * Make text sparate with - (kebabcase).
     *
     * @param string $text input text
     *
     * @return string
     */
    public static function toKebabCase(string $text)
    {
        return \str_replace([' ', '-', '_', '+'], '-', $text);
    }

    /**
     * Make text each word start with capital (pascalcase).
     *
     * @param string $text input text
     *
     * @return string
     */
    public static function toPascalCase(string $text)
    {
        $space_case  = \str_replace(['-', '_', '+'], ' ', $text);
        $first_upper = static::firstUpperAll($space_case);

        return str_replace(' ', '', $first_upper);
    }

    /**
     * Make text camelcase.
     *
     * @param string $text input text
     *
     * @return string
     */
    public static function toCamelCase(string $text)
    {
        $space_case  = \str_replace(['-', '_', '+'], ' ', $text);
        $arr_text    = explode(' ', $space_case);
        $result      = [];
        $first_text  = true;

        foreach ($arr_text as $text) {
            if ($first_text) {
                $result[]   = mb_strtolower($text);
                $first_text = false;
                continue;
            }

            $result[] = ucfirst($text);
        }

        return implode('', $result);
    }

    /**
     * Make slugify (url-title).
     *
     * @param string $text inpu text
     *
     * @return string
     *
     * @throw NoReturn
     */
    public static function slug(string $text)
    {
        $original = $text;
        // replace non letter or digits by -
        $text = \preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = \iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = \preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = \trim($text, '-');

        // remove duplicate -
        $text = \preg_replace('~-+~', '-', $text);

        // lowercase
        $text = mb_strtolower($text);

        if (empty($text)) {
            throw new NoReturn(__FUNCTION__, $original);
        }

        return $text;
    }

    /**
     * Make muliple text (repeat).
     *
     * @param string $text     Text
     * @param int    $multiple Number reapet (less that 0 will return empty)
     *
     * @return string
     */
    public static function repeat(string $text, int $multiple)
    {
        return \str_repeat($text, $multiple);
    }

    /**
     * Get string length (0 if empty).
     *
     * @return int
     */
    public static function length(string $text)
    {
        return \strlen($text);
    }

    /**
     * Render template text.
     *
     * @param string                $template        Template string text
     * @param array<string, string> $data            String data template (match with $template)
     * @param string                $open_delimeter  Open delimeter (rekomend use: '{')
     * @param string                $close_delimeter Open delimeter (rekomend use: '}')
     *
     * @return string Template pass with math data
     */
    public static function template(string $template, array $data, string $open_delimeter = '{', string $close_delimeter = '}')
    {
        if ('{' === $open_delimeter && '}' === $close_delimeter) {
            $template = preg_replace(['/\\{\s+/', '/\s+\\}/'], ['{', '}'], $template);
        }

        $keys = [];
        foreach ($data as $key => $value) {
            $keys[] = $open_delimeter . $key . $close_delimeter;
        }

        return \str_replace($keys, $data, $template);
    }

    /**
     * Fill string (start) with string if length is less.
     *
     * @param string $text       String Text
     * @param string $fill       String fill for miss length
     * @param int    $max_length max length of output string
     *
     * @return string
     */
    public static function fill(string $text, string $fill, int $max_length)
    {
        return \str_pad($text, $max_length, $fill, STR_PAD_LEFT);
    }

    /**
     * Fill string (end) with string if length is less.
     *
     * @param string $text       String text
     * @param string $fill       String fill for miss length
     * @param int    $max_length max length of output string
     *
     * @return string
     */
    public static function fillEnd(string $text, string $fill, int $max_length)
    {
        return \str_pad($text, $max_length, $fill, STR_PAD_RIGHT);
    }

    /**
     * Create mask string.
     *
     * @param string $text        String text
     * @param string $mask        Mask
     * @param int    $start       Start postion mask
     * @param int    $mask_length Mask lenght
     *
     * @return string String with mask
     */
    public static function mask(string $text, string $mask, int $start, int $mask_length = 9999)
    {
        // negative postion, count from end text
        if ($start < 0) {
            $start = \strlen($text) + $start;
        }

        $end = $start + $mask_length;
        $start--;
        $arr_text = \preg_split('//', $text, -1, PREG_SPLIT_NO_EMPTY);
        $new_text = array_map(function ($index, $string) use ($mask, $start, $end) {
            if ($index > $start && $index < $end) {
                return $mask;
            }

            return $string;
        }, array_keys($arr_text), array_values($arr_text));

        return \implode('', $new_text);
    }

    // condition ------------------------------------

    /**
     * Check determinate input is string.
     *
     * @param string $text Text
     *
     * @return bool
     */
    public static function isString($text)
    {
        return \is_string($text);
    }

    /**
     * Check string is empty string.
     *
     * @return bool
     *  */
    public static function isEmpty(string $text)
    {
        return '' === $text;
    }

    /**
     * Retreves the matches of string against a search pattern.
     *
     * @param string $text    String
     * @param string $pattern String leguler expresstion
     *
     * @return bool
     */
    public static function isMatch(string $text, string $pattern)
    {
        $has_result = \preg_match($pattern, $text);

        if (1 === $has_result) {
            return true;
        }

        return false;
    }

    /**
     * Retreves the matches of string against a search pattern.
     * Short hand for `isMatch`.
     *
     * @param string $text    String
     * @param string $pattern String leguler expresstion
     *
     * @return bool
     */
    public static function is(string $text, string $pattern)
    {
        return static::isMatch($text, $pattern);
    }

    // Backward Compatible php 8.0 --------------------------------

    /**
     * Check text contain with.
     *
     * @param string $text Text
     * @param string $find Text contain
     *
     * @return bool True if text contain
     *
     * @see https://github.com/symfony/polyfill-php80/blob/main/Php80.php
     */
    public static function contains(string $text, string $find)
    {
        return '' === $find || false !== mb_strpos($text, $find);
    }

    /**
     * Check text starts with with.
     *
     * @param string $text       Text
     * @param string $start_with Start with
     *
     * @return bool True if text starts with
     *
     * @see https://github.com/symfony/polyfill-php80/blob/main/Php80.php
     */
    public static function startsWith(string $text, string $start_with)
    {
        return 0 === \strncmp($text, $start_with, \strlen($start_with));
    }

    /**
     * Check text ends with with.
     *
     * @param string $text       Text
     * @param string $start_with Start with
     *
     * @return bool True if text ends with
     *
     * @see https://github.com/symfony/polyfill-php80/blob/main/Php80.php
     */
    public static function endsWith(string $text, string $start_with)
    {
        if ('' === $start_with || $start_with === $text) {
            return true;
        }

        if ('' === $text) {
            return false;
        }

        $needleLength = \strlen($start_with);

        return $needleLength <= \strlen($text) && 0 === \substr_compare($text, $start_with, -$needleLength);
    }

    /**
     * Truncate text to limited length.
     *
     * @param string $text              Text
     * @param int    $length            Maximum text length
     * @param string $truncate_caracter Truncate caracter
     *
     * @return string
     */
    public static function limit(string $text, int $length, string $truncate_caracter = '...')
    {
        return self::slice($text, 0, $length) . $truncate_caracter;
    }
}
