<?php

declare(strict_types=1);

namespace App\Services\Media\DTO;

final class ImageResult
{
    public bool $processed = false;

    public ?string $optimizedFilename = null;

    public ?string $thumbnailFilename = null;

    public ?int $width = null;

    public ?int $height = null;

    public ?int $quality = null;

    public bool $convertedToWebp = false;
}