<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\Media;

final class MediaService
{
    public function __construct(private readonly Media $media, private readonly UploadService $uploads)
    {
    }

    public function upload(array $file, array $meta, int $userId): int
    {
        $upload = $this->uploads->upload($file);

        return $this->media->create([
            'disk' => $upload->disk,
            'folder' => $upload->folder,
            'filename' => $upload->filename,
            'original_name' => $upload->originalName,
            'extension' => $upload->extension,
            'mime_type' => $upload->mimeType,
            'file_size' => $upload->fileSize,
            'width' => $upload->width,
            'height' => $upload->height,
            'original_width' => $upload->width,
            'original_height' => $upload->height,
            'title' => trim((string) ($meta['title'] ?? '')),
            'alt_text' => trim((string) ($meta['alt_text'] ?? '')),
            'caption' => trim((string) ($meta['caption'] ?? '')),
            'description' => trim((string) ($meta['description'] ?? '')),
            'uploaded_by' => $userId,
        ]);
    }

    public function paginate(int $page, int $perPage, string $search): array
    {
        return ['items' => $this->media->paginate($page, $perPage, $search), 'total' => $this->media->total($search)];
    }

    public function delete(int $id): bool
    {
        return $this->media->delete($id);
    }
}
