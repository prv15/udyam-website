<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Services\Media\DTO\UploadResult;
use RuntimeException;

class ImageService
{
    /**
     * Large image width
     */
    private const LARGE_WIDTH = 1600;

    /**
     * Thumbnail width
     */
    private const THUMB_WIDTH = 300;

    /**
     * WebP Quality
     */
    private const QUALITY = 85;

    /**
     * Process uploaded image.
     */
    public function process(UploadResult $upload): array
    {
        if (!$upload->success) {
            throw new RuntimeException('Upload was not successful.');
        }

        if (!str_starts_with($upload->mimeType, 'image/')) {

            return [
                'optimized' => null,
                'thumbnail' => null,
                'width' => null,
                'height' => null
            ];
        }

        $year = date('Y');
        $month = date('m');

        $largeDir = PUBLIC_PATH . "/uploads/media/large/{$year}/{$month}";
        $thumbDir = PUBLIC_PATH . "/uploads/media/thumb/{$year}/{$month}";

        $this->createDirectory($largeDir);
        $this->createDirectory($thumbDir);

        $base = pathinfo($upload->filename, PATHINFO_FILENAME);

        $largeName = $base . '.webp';
        $thumbName = $base . '_thumb.webp';

        $largePath = $largeDir . '/' . $largeName;
        $thumbPath = $thumbDir . '/' . $thumbName;

        $image = $this->createImage(
            $upload->path,
            $upload->mimeType
        );

        $image = $this->fixOrientation(
            $image,
            $upload->path,
            $upload->mimeType
        );

        [$newWidth, $newHeight] = $this->resize(
            $image,
            $largePath,
            self::LARGE_WIDTH
        );

        $this->resize(
            $image,
            $thumbPath,
            self::THUMB_WIDTH
        );

        imagedestroy($image);

        return [

            'optimized_filename' => $largeName,

            'thumbnail_filename' => $thumbName,

            'width' => $newWidth,

            'height' => $newHeight

        ];
    }

    /**
     * Create GD image.
     */
    private function createImage(
        string $path,
        string $mime
    )
    {
        return match ($mime) {

            'image/jpeg'
                => imagecreatefromjpeg($path),

            'image/png'
                => imagecreatefrompng($path),

            'image/webp'
                => imagecreatefromwebp($path),

            'image/gif'
                => imagecreatefromgif($path),

            default
                => throw new RuntimeException(
                    'Unsupported image type.'
                )
        };
    }

    /**
     * Resize and convert to WebP.
     */
    private function resize(
        $source,
        string $destination,
        int $maxWidth
    ): array {

        $width = imagesx($source);

        $height = imagesy($source);

        if ($width <= $maxWidth) {

            imagewebp(
                $source,
                $destination,
                self::QUALITY
            );

            return [$width, $height];
        }

        $ratio = $height / $width;

        $newWidth = $maxWidth;

        $newHeight = (int) round(
            $newWidth * $ratio
        );

        $canvas = imagecreatetruecolor(
            $newWidth,
            $newHeight
        );

        imagealphablending(
            $canvas,
            false
        );

        imagesavealpha(
            $canvas,
            true
        );

        imagecopyresampled(

            $canvas,

            $source,

            0,

            0,

            0,

            0,

            $newWidth,

            $newHeight,

            $width,

            $height

        );

        imagewebp(
            $canvas,
            $destination,
            self::QUALITY
        );

        imagedestroy($canvas);

        return [

            $newWidth,

            $newHeight

        ];
    }

    /**
     * Fix JPEG orientation.
     */
    private function fixOrientation(
        $image,
        string $path,
        string $mime
    )
    {
        if ($mime !== 'image/jpeg') {
            return $image;
        }

        if (!function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path);

        if (!$exif || empty($exif['Orientation'])) {
            return $image;
        }

        switch ($exif['Orientation']) {

            case 3:

                $image = imagerotate(
                    $image,
                    180,
                    0
                );

                break;

            case 6:

                $image = imagerotate(
                    $image,
                    -90,
                    0
                );

                break;

            case 8:

                $image = imagerotate(
                    $image,
                    90,
                    0
                );

                break;
        }

        return $image;
    }

    /**
     * Create folder.
     */
    private function createDirectory(
        string $dir
    ): void {

        if (!is_dir($dir)) {

            mkdir(
                $dir,
                0755,
                true
            );
        }
    }
}
