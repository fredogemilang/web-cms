<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CMS Version
    |--------------------------------------------------------------------------
    |
    | This value is the version of the CMS. This is used for plugin
    | dependency validation to ensure plugins are compatible with
    | the current CMS version.
    |
    */
    'version' => '1.0.0',

    /*
    |--------------------------------------------------------------------------
    | Admin Path
    |--------------------------------------------------------------------------
    |
    | The URI path for the admin panel. This can be customized
    | for security purposes.
    |
    */
    'path' => env('ADMIN_PATH', 'ctrlpanel'),
];
