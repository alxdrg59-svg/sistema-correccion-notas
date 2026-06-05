<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Disco de Almacenamiento por Defecto
    |--------------------------------------------------------------------------
    |
    | Aqui se especifica el disco de almacenamiento que el framework usara
    | por defecto. El disco "local" y varios discos en la nube estan
    | disponibles para almacenar archivos de tu aplicacion.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Discos de Almacenamiento
    |--------------------------------------------------------------------------
    |
    | Aqui puedes configurar tantos discos como necesites, e incluso puedes
    | configurar multiples discos con el mismo driver. Se incluyen ejemplos
    | de los drivers de almacenamiento mas comunes como referencia.
    |
    | Drivers soportados: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        'gcs' => [
            'driver' => 'gcs',
            'project_id' => env('GOOGLE_CLOUD_PROJECT_ID'),
            'bucket' => env('GOOGLE_CLOUD_STORAGE_BUCKET'),
            'path_prefix' => env('GOOGLE_CLOUD_STORAGE_PATH_PREFIX', ''),
            'visibility' => 'private',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Enlaces Simbolicos
    |--------------------------------------------------------------------------
    |
    | Aqui puedes configurar los enlaces simbolicos que se crearan cuando
    | se ejecute el comando `storage:link` de Artisan. Las claves del
    | arreglo son las ubicaciones de los enlaces y los valores son
    | los directorios destino.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
