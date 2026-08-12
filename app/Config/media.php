<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Storage Disk
    |--------------------------------------------------------------------------
    */
    'disk' => env('MEDIA_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Base Upload Directory
    |--------------------------------------------------------------------------
    */
    'upload_path' => PUBLIC_PATH . '/uploads/media',

    // Existing hosting accounts normally own this directory. Local XAMPP
    // runs Apache as a separate user, so the deployed directory itself must
    // grant that user write access (the uploader still creates children 0775).
    'directory_mode' => 0775,

    /*
    |--------------------------------------------------------------------------
    | Maximum Upload Size (Bytes)
    |--------------------------------------------------------------------------
    */
    'max_upload_size' => 25 * 1024 * 1024, // 25 MB

    /*
    |--------------------------------------------------------------------------
    | Image Processing
    |--------------------------------------------------------------------------
    */
    'image' => [

        'quality' => 85,

        'large_width' => 1600,

        'thumb_width' => 300,

        'webp_quality' => 85,

        'auto_rotate' => true,

        'strip_metadata' => true,

        'upscale' => false,

        'create_webp' => true,

    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed MIME Types
    |--------------------------------------------------------------------------
    */
    'allowed_mime_types' => [

        // Images
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',

        // Documents
        'application/pdf',

        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',

        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',

        'text/plain',
        'text/csv',

        'application/zip',
        'application/x-zip-compressed',

    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Extensions
    |--------------------------------------------------------------------------
    */
    'allowed_extensions' => [

        'jpg',
        'jpeg',
        'png',
        'gif',
        'webp',

        'pdf',

        'doc',
        'docx',

        'xls',
        'xlsx',

        'csv',
        'txt',

        'zip',

    ],

    /*
    |--------------------------------------------------------------------------
    | Image Extensions
    |--------------------------------------------------------------------------
    */
    'image_extensions' => [

        'jpg',
        'jpeg',
        'png',
        'gif',
        'webp',

    ],

    /*
    |--------------------------------------------------------------------------
    | Folder Structure
    |--------------------------------------------------------------------------
    */
    'folders' => [

        'original' => 'original',

        'large' => 'large',

        'thumb' => 'thumb',

        'temp' => 'temp',

    ],

    /*
    |--------------------------------------------------------------------------
    | Filename Generation
    |--------------------------------------------------------------------------
    */
    'filename' => [

        'random_length' => 40,

        'preserve_original_name' => true,

    ],

    /*
    |--------------------------------------------------------------------------
    | Duplicate Detection
    |--------------------------------------------------------------------------
    */
    'duplicate_detection' => [

        'enabled' => true,

        'algorithm' => 'sha1',

    ],

];
