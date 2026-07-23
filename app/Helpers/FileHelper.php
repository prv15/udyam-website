<?php

declare(strict_types=1);

namespace App\Helpers;

class FileHelper
{
    /**
     * Format file size.
     */
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = [
            'B',
            'KB',
            'MB',
            'GB',
            'TB',
            'PB'
        ];

        $power = min(
            (int) floor(log($bytes, 1024)),
            count($units) - 1
        );

        return sprintf(
            "%01.{$precision}f %s",
            $bytes / (1024 ** $power),
            $units[$power]
        );
    }

    /**
     * Get extension from filename.
     */
    public static function extension(string $filename): string
    {
        return strtolower(
            pathinfo($filename, PATHINFO_EXTENSION)
        );
    }

    /**
     * Check whether the file is an image.
     */
    public static function isImage(string $mimeType): bool
    {
        return str_starts_with(
            strtolower($mimeType),
            'image/'
        );
    }

    /**
     * Human readable mime category.
     */
    public static function category(string $mimeType): string
    {
        if (self::isImage($mimeType)) {
            return 'Image';
        }

        if (str_starts_with($mimeType, 'video/')) {
            return 'Video';
        }

        if (str_starts_with($mimeType, 'audio/')) {
            return 'Audio';
        }

        if ($mimeType === 'application/pdf') {
            return 'PDF';
        }

        return 'Document';
    }

    /**
     * Bootstrap icon class.
     */
    public static function icon(string $extension): string
    {
        return match (strtolower($extension)) {

            'pdf' => 'bi-file-earmark-pdf',

            'doc',
            'docx' => 'bi-file-earmark-word',

            'xls',
            'xlsx' => 'bi-file-earmark-excel',

            'ppt',
            'pptx' => 'bi-file-earmark-ppt',

            'zip',
            'rar',
            '7z' => 'bi-file-earmark-zip',

            'mp4',
            'avi',
            'mov',
            'mkv' => 'bi-file-earmark-play',

            'mp3',
            'wav' => 'bi-file-earmark-music',

            default => 'bi-file-earmark'
        };
    }
}