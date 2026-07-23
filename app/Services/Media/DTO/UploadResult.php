<?php

declare(strict_types=1);

namespace App\Services\Media\DTO;

class UploadResult
{
    public bool $success = false;

    public string $disk = 'local';

    public string $folder = '';

    public string $filename = '';

    public string $originalName = '';

    public string $extension = '';

    public string $mimeType = '';

    public int $fileSize = 0;

    public ?int $width = null;

    public ?int $height = null;

    public string $path = '';

    public ?string $error = null;
}
