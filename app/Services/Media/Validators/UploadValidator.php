<?php

declare(strict_types=1);

namespace App\Services\Media\Validators;

use App\Core\Config;
use App\Services\Media\Exceptions\UploadException;

final class UploadValidator
{
    public function validate(array $file): void
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new UploadException('The file could not be uploaded.');
        }

        $tmpName = $file['tmp_name'] ?? '';
        if (!is_string($tmpName) || $tmpName === '' || !is_uploaded_file($tmpName)) {
            throw new UploadException('Invalid upload payload.');
        }

        $config = Config::get('media');
        $size = (int) ($file['size'] ?? 0);
        if ($size < 1 || $size > (int) $config['max_upload_size']) {
            throw new UploadException('The file exceeds the allowed upload size.');
        }

        $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if ($extension === '' || !in_array($extension, $config['allowed_extensions'], true)) {
            throw new UploadException('This file extension is not allowed.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($tmpName);
        if ($mimeType === false || !in_array($mimeType, $config['allowed_mime_types'], true)) {
            throw new UploadException('This file type is not allowed.');
        }
    }
}
