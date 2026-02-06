<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Media Storage Settings
    |--------------------------------------------------------------------------
    */
    'disk' => env('MEDIA_DISK', 'public'),
    'path' => 'media',

    /*
    |--------------------------------------------------------------------------
    | Allowed File Types
    |--------------------------------------------------------------------------
    */
    'allowed_mimes' => [
        // Images
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
        
        // Documents
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        
        // Archives
        'application/zip',
        'application/x-rar-compressed',
    ],

    'allowed_extensions' => [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg',
        'pdf', 'doc', 'docx', 'xls', 'xlsx',
        'zip', 'rar',
    ],

    /*
    |--------------------------------------------------------------------------
    | File Size Limits
    |--------------------------------------------------------------------------
    | Maximum file size in kilobytes
    */
    'max_file_size' => env('MEDIA_MAX_FILE_SIZE', 10240), // 10MB default

    /*
    |--------------------------------------------------------------------------
    | WebP Conversion Settings
    |--------------------------------------------------------------------------
    */
    'webp' => [
        'enabled' => env('MEDIA_WEBP_ENABLED', true),
        'quality' => env('MEDIA_WEBP_QUALITY', 80),
        'convert_types' => ['image/jpeg', 'image/jpg', 'image/png'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Processing
    |--------------------------------------------------------------------------
    */
    'image_driver' => env('MEDIA_IMAGE_DRIVER', 'gd'), // 'gd' or 'imagick'
];
