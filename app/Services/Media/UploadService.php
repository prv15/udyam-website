<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Core\Config;
use App\Services\Media\DTO\UploadResult;
use App\Services\Media\Exceptions\UploadException;
use App\Services\Media\Validators\UploadValidator;

final class UploadService
{
    public function __construct(private readonly UploadValidator $validator)
    {
    }

    public function upload(array $file): UploadResult
    {
        $this->validator->validate($file);
        $config = Config::get('media');
        $folder = date('Y/m');
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = bin2hex(random_bytes(20)) . '.' . $extension;
        $directory = rtrim($config['upload_path'], DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . $config['folders']['original']
            . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $folder);

        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new UploadException('Unable to prepare the upload directory.');
        }

        $path = $directory . DIRECTORY_SEPARATOR . $filename;
        if (!move_uploaded_file($file['tmp_name'], $path)) {
            throw new UploadException('Unable to save the uploaded file.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $result = new UploadResult();
        $result->success = true;
        $result->disk = $config['disk'];
        $result->folder = 'original/' . $folder;
        $result->filename = $filename;
        $result->originalName = basename($file['name']);
        $result->extension = $extension;
        $result->mimeType = (string) $finfo->file($path);
        $result->fileSize = (int) filesize($path);
        $result->path = $path;

        if (str_starts_with($result->mimeType, 'image/')) {
            $dimensions = @getimagesize($path);
            if ($dimensions !== false) {
                $result->width = $dimensions[0];
                $result->height = $dimensions[1];
            }
        }

        return $result;
    }
}
