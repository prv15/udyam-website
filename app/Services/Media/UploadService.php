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

        $this->prepareDirectory($directory, (int) ($config['directory_mode'] ?? 0775));

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

    private function prepareDirectory(string $directory, int $mode): void
    {
        if (!is_dir($directory)) {
            $previousUmask = umask(0002);
            try {
                $created = @mkdir($directory, $mode, true);
            } finally {
                umask($previousUmask);
            }

            if (!$created && !is_dir($directory)) {
                error_log('Media upload directory creation failed: ' . $directory);
                throw new UploadException('The Media Library folder is not writable. Please check the uploads/media directory permissions.');
            }
        }

        if (!is_writable($directory)) {
            error_log('Media upload directory is not writable: ' . $directory);
            throw new UploadException('The Media Library folder is not writable. Please check the uploads/media directory permissions.');
        }
    }
}
