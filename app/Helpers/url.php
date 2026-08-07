<?php

declare(strict_types=1);

use App\Config\App;

if (!function_exists('url')) {
    /**
     * Build an application-relative URL for the current deployment.
     */
    function url(string $path = '/'): string
    {
        // A bare in-page anchor (e.g. "#contact-details") must stay untouched.
        // Prefixing it with '/' turns it into an absolute link to the site
        // root, which navigates away to the homepage instead of scrolling
        // to the section on the current page.
        if (str_starts_with($path, '#')) {
            return $path;
        }

        $basePath = App::basePath();
        $path = '/' . ltrim($path, '/');

        return ($basePath === '' ? '' : $basePath) . $path;
    }
}

if (!function_exists('public_url')) {
    /**
     * Normalize a stored public path or URL.
     *
     * This keeps legacy database values such as
     * /udyamventures/uploads/... or /demo/uploads/... working after the
     * application is moved to the domain root.
     */
    function public_url(?string $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        // Preserve third-party absolute URLs. For this application's own old
        // localhost/domain URLs, retain only the public path and normalize it.
        if (preg_match('#^https?://#i', $value) === 1) {
            $parts = parse_url($value);
            $host = strtolower((string) ($parts['host'] ?? ''));
            $currentHost = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));

            if ($host !== '' && $host !== 'localhost' && $host !== '127.0.0.1' && $currentHost !== '' && $host !== $currentHost) {
                return $value;
            }

            $value = (string) ($parts['path'] ?? '/');
            if (!empty($parts['query'])) {
                $value .= '?' . $parts['query'];
            }
            if (!empty($parts['fragment'])) {
                $value .= '#' . $parts['fragment'];
            }
        }

        // Remove known historical deployment prefixes from stored values.
        $value = preg_replace('#^/(?:udyamventures|demo)(?=/|$)#i', '', $value) ?? $value;

        return url('/' . ltrim($value, '/'));
    }
}

if (!function_exists('media_url')) {
    function media_url(array $media): string
    {
        return public_url('/uploads/media/' . trim((string) $media['folder'], '/') . '/' . rawurlencode((string) $media['filename']));
    }
}
