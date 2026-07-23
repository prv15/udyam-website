<?php

namespace App\Core\Support;

class Str
{
    /**
     * Generate URL-friendly slug.
     */
    public static function slug(string $text): string
    {
        $text = strtolower(trim($text));

        $text = preg_replace('/[^a-z0-9]+/i', '-', $text);

        return trim($text, '-');
    }

    /**
     * Limit text length.
     */
    public static function limit(string $text, int $limit = 100): string
    {
        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        return mb_substr($text, 0, $limit) . '...';
    }

    /**
     * Generate random string.
     */
    public static function random(int $length = 16): string
    {
        return substr(bin2hex(random_bytes($length)), 0, $length);
    }

    /**
     * Convert to Title Case.
     */
    public static function title(string $text): string
    {
        return ucwords(strtolower($text));
    }
}