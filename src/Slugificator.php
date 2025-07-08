<?php

namespace Miakiwi\Kiwiquill;



class Slugificator
{
    /**
     * Convert a string to a URL-friendly slug.
     * @param mixed $text The text to convert to a slug.
     * @param string $divider The character to use as a divider in the slug (default is '-').
     * @return string The slugified version of the input text.
     * 
     * Adapted from https://stackoverflow.com/a/2955878 to preserve file extensions and URL path separators.
     */
    public static function slugify($text, string $divider = '-')
    {
        // Extract the file extension if it exists
        $extension = '';

        if (is_string($text) && str_contains($text, '.')) {
            $parts = explode('.', $text);
            $extension = array_pop($parts);
            $text = implode('.', $parts);
        }

        // replace non letter or digits by divider
        $text = preg_replace('~[^\pL\d/]+~u', $divider, $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w/]+~', '', $text);

        // trim
        $text = trim($text, $divider);

        // remove duplicate divider
        $text = preg_replace('~-+~', $divider, $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text . ($extension ? '.' . $extension : '');
    }



    /**
     * Convert an array of strings to a slugified path.
     * @param array $parts The array of strings to convert to a slugified path.
     * @param string $divider The character to use as a divider in the slugified path (default is '-').
     * @return string The slugified path created from the input array.
     */
    public static function slugifyPath(array $parts, string $divider = '-'): string
    {
        $slugifiedParts = array_map(function ($part) use ($divider) {
            return self::slugify($part, $divider);
        }, $parts);

        return implode('/', $slugifiedParts);
    }
}