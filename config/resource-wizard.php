<?php

declare(strict_types=1);

return [
    'api-path' => env('RESOURCE_WIZARD_API_PATH', 'resources/api'),
    'database' => [
        'connection' => env('RESOURCE_WIZARD_DATABASE_CONNECTION', env('DB_CONNECTION', 'mysql')),
        'prefix' => env('RESOURCE_WIZARD_DATABASE_PREFIX', 'wizard_'),
    ],
    'users' => [
        'model' => env('RESOURCE_WIZARD_USERS_MODEL', 'App\Models\User'),
        'table' => env('RESOURCE_WIZARD_USERS_TABLE', 'users'),
        'id' => [
            'column' => env('RESOURCE_WIZARD_USERS_ID_COLUMN', 'id'),
            'type' => env('RESOURCE_WIZARD_USERS_ID_TYPE', 'bigInteger'),
        ],
    ],
];
