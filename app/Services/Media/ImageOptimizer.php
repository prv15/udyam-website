<?php

declare(strict_types=1);

namespace App\Services\Media;

use GdImage;
use App\Core\Config;
use App\Services\Media\DTO\ImageResult;
use App\Services\Media\DTO\UploadResult;
use App\Services\Media\Exceptions\ImageException;

final class ImageOptimizer
{
    /**
     * Media configuration.
     */
    private array $config;

    /**
     * Base upload path.
     */
    private string $basePath;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->config = Config::get('media');

        $this->basePath = rtrim(
            $this->config['upload_path'],
            DIRECTORY_SEPARATOR
        );
    }

    /**
     * Process an uploaded image.
     *
     * Non-image files are ignored and returned as processed.
     *
     * @throws ImageException
     */
    public function process(UploadResult $upload): ImageResult
    {
        $result = new ImageResult();


        /*
        |--------------------------------------------------------------------------
        | Skip Non Image Files
        |--------------------------------------------------------------------------
        */

        if (!$this->isImage($upload->mimeType)) {

            $result->processed = true;

            return $result;
        }

        /*
        |--------------------------------------------------------------------------
        | Verify Original File Exists
        |--------------------------------------------------------------------------
        */

        if (!is_file($upload->path)) {

            throw new ImageException(
                'Original uploaded file could not be found.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Build Destination Directories
        |--------------------------------------------------------------------------
        */

        $largeDirectory =
            $this->basePath .
            DIRECTORY_SEPARATOR .
            $this->config['folders']['large'] .
            DIRECTORY_SEPARATOR .
            $upload->folder;

        $thumbDirectory =
            $this->basePath .
            DIRECTORY_SEPARATOR .
            $this->config['folders']['thumb'] .
            DIRECTORY_SEPARATOR .
            $upload->folder;

        $this->ensureDirectory($largeDirectory);

        $this->ensureDirectory($thumbDirectory);

        /*
        |--------------------------------------------------------------------------
        | Generate Destination Filenames
        |--------------------------------------------------------------------------
        */

        $basename = pathinfo(
            $upload->filename,
            PATHINFO_FILENAME
        );

        $result->optimizedFilename = $basename . '.webp';

        $result->thumbnailFilename = $basename . '_thumb.webp';

        /*
        |--------------------------------------------------------------------------
        | Paths
        |--------------------------------------------------------------------------
        */

        $largePath =
            $largeDirectory .
            DIRECTORY_SEPARATOR .
            $result->optimizedFilename;

        $thumbPath =
            $thumbDirectory .
            DIRECTORY_SEPARATOR .
            $result->thumbnailFilename;

        /*
        |--------------------------------------------------------------------------
        | Remaining Processing
        |--------------------------------------------------------------------------
        |
        | Next Steps
        |
        | 1. Load GD image
        | 2. Fix EXIF orientation
        | 3. Resize large image
        | 4. Save WebP
        | 5. Generate thumbnail
        | 6. Populate ImageResult
        |
        */
        $this->checkMemoryLimit($upload->path);
        $image = $this->createImage(
    $upload->path,
    $upload->mimeType
);
$image = $this->applyExifOrientation(
    $image,
    $upload
);
    }
    /**
 * Ensure enough memory is available before loading the image.
 *
 * @throws ImageException
 */
private function checkMemoryLimit(string $path): void
{
    $info = @getimagesize($path);

    if ($info === false) {
        throw new ImageException(
            'Unable to read image information.'
        );
    }

    $width = $info[0];
    $height = $info[1];

    /*
     * Approximate memory needed:
     * width × height × 4 bytes (RGBA)
     * plus 5 MB safety margin.
     */
    $required = ($width * $height * 4) + (5 * 1024 * 1024);

    $limit = $this->memoryLimitBytes();

    if ($limit > 0 && memory_get_usage(true) + $required > $limit) {
        throw new ImageException(
            'Image is too large to process with the available PHP memory.'
        );
    }
}

/**
 * Convert php.ini memory_limit to bytes.
 */
private function memoryLimitBytes(): int
{
    $value = ini_get('memory_limit');

    if ($value === false || $value === '-1') {
        return -1;
    }

    $value = trim($value);

    $unit = strtolower(substr($value, -1));

    $bytes = (int) $value;

    return match ($unit) {
        'g' => $bytes * 1024 * 1024 * 1024,
        'm' => $bytes * 1024 * 1024,
        'k' => $bytes * 1024,
        default => $bytes,
    };
}
/**
 * Apply EXIF orientation correction.
 */
private function applyExifOrientation(
    GdImage $image,
    UploadResult $upload
): GdImage {

    if (
        !$this->config['image']['auto_rotate']
        || $upload->mimeType !== 'image/jpeg'
        || !function_exists('exif_read_data')
    ) {
        return $image;
    }

    $exif = @exif_read_data($upload->path);

    if (
        !is_array($exif)
        || empty($exif['Orientation'])
    ) {
        return $image;
    }

    return $this->orientImage(
        $image,
        (int) $exif['Orientation']
    );
}
/**
 * Correct image orientation.
 */
private function orientImage(
    GdImage $image,
    int $orientation
): GdImage {

    return match ($orientation) {

        2 => $this->flipHorizontal($image),

        3 => imagerotate($image, 180, 0),

        4 => $this->flipVertical($image),

        5 => $this->flipVertical(
                imagerotate($image, -90, 0)
             ),

        6 => imagerotate($image, -90, 0),

        7 => $this->flipHorizontal(
                imagerotate($image, -90, 0)
             ),

        8 => imagerotate($image, 90, 0),

        default => $image,
    };
}
/**
 * Horizontal flip.
 */
private function flipHorizontal(
    GdImage $image
): GdImage {

    if (function_exists('imageflip')) {

        imageflip(
            $image,
            IMG_FLIP_HORIZONTAL
        );

        return $image;
    }

    return $this->manualFlip(
        $image,
        true
    );
}
/**
 * Vertical flip.
 */
private function flipVertical(
    GdImage $image
): GdImage {

    if (function_exists('imageflip')) {

        imageflip(
            $image,
            IMG_FLIP_VERTICAL
        );

        return $image;
    }

    return $this->manualFlip(
        $image,
        false
    );
}
/**
 * Manual flip fallback.
 */
private function manualFlip(
    GdImage $image,
    bool $horizontal
): GdImage {

    $width = imagesx($image);

    $height = imagesy($image);

    $flipped = imagecreatetruecolor(
        $width,
        $height
    );

    imagealphablending(
        $flipped,
        false
    );

    imagesavealpha(
        $flipped,
        true
    );

    if ($horizontal) {

        for ($x = 0; $x < $width; $x++) {

            imagecopy(

                $flipped,

                $image,

                $width - $x - 1,

                0,

                $x,

                0,

                1,

                $height

            );
        }

    } else {

        for ($y = 0; $y < $height; $y++) {

            imagecopy(

                $flipped,

                $image,

                0,

                $height - $y - 1,

                0,

                $y,

                $width,

                1

            );
        }
    }

    imagedestroy($image);

    return $flipped;


        return $result;
    }

    /**
     * Determine whether uploaded file is an image.
     */
    private function isImage(string $mime): bool
    {
        return str_starts_with($mime, 'image/');
    }

    /**
     * Ensure directory exists.
     *
     * @throws ImageException
     */
    private function ensureDirectory(string $directory): void
    {
        if (is_dir($directory)) {
            return;
        }

        if (!mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new ImageException(
                "Unable to create directory: {$directory}"
            );
        }
    }
        /**
     * Create a GD image instance from a file.
     *
     * @throws ImageException
     */
    private function createImage(
        string $path,
        string $mime
    ): GdImage {

        return match ($mime) {

            'image/jpeg' => $this->createJpeg($path),

            'image/png'  => $this->createPng($path),

            'image/gif'  => $this->createGif($path),

            'image/webp' => $this->createWebp($path),

            default => throw new ImageException(
                "Unsupported image type: {$mime}"
            ),
        };
    }

    /**
     * Create JPEG image.
     *
     * @throws ImageException
     */
    private function createJpeg(string $path): GdImage
    {
        $image = @imagecreatefromjpeg($path);

        if (!$image) {
            throw new ImageException(
                'Unable to load JPEG image.'
            );
        }

        return $image;
    }

    /**
     * Create PNG image.
     *
     * @throws ImageException
     */
    private function createPng(string $path): GdImage
    {
        $image = @imagecreatefrompng($path);

        if (!$image) {
            throw new ImageException(
                'Unable to load PNG image.'
            );
        }

        imagealphablending($image, true);

        imagesavealpha($image, true);

        return $image;
    }

    /**
     * Create GIF image.
     *
     * @throws ImageException
     */
    private function createGif(string $path): GdImage
    {
        if ($this->isAnimatedGif($path)) {
            throw new ImageException(
                'Animated GIF optimization is not supported.'
            );
        }

        $image = @imagecreatefromgif($path);

        if (!$image) {
            throw new ImageException(
                'Unable to load GIF image.'
            );
        }

        return $image;
    }

    /**
     * Create WebP image.
     *
     * @throws ImageException
     */
    private function createWebp(string $path): GdImage
    {
        if (!function_exists('imagecreatefromwebp')) {
            throw new ImageException(
                'WebP is not supported by GD.'
            );
        }

        $image = @imagecreatefromwebp($path);

        if (!$image) {
            throw new ImageException(
                'Unable to load WebP image.'
            );
        }

        return $image;
    }

    /**
     * Detect animated GIF.
     */
    private function isAnimatedGif(string $path): bool
    {
        $contents = @file_get_contents(
            $path,
            false,
            null,
            0,
            200000
        );

        if ($contents === false) {
            return false;
        }

        return preg_match(
            '#\x00\x21\xF9\x04.{4}\x00[\x2C\x21]#s',
            $contents,
            $matches
        ) > 1;
    }
}