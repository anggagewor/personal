<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Audit Log Driver
    |--------------------------------------------------------------------------
    |
    | Driver yang digunakan untuk menyimpan audit log.
    | Supported: "database"
    | Coming soon: "json", "s3"
    |
    */

    'driver' => env('AUDIT_LOG_DRIVER', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Driver Configurations
    |--------------------------------------------------------------------------
    */

    'drivers' => [

        'database' => [
            'connection' => env('AUDIT_LOG_DB_CONNECTION', null),
            'table' => 'audit_logs',
        ],

        // 'json' => [
        //     'path' => storage_path('logs/audit'),
        //     'daily' => true,
        // ],

        // 's3' => [
        //     'disk' => 's3',
        //     'prefix' => 'audit/',
        //     'batch_size' => 100,
        // ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Retention
    |--------------------------------------------------------------------------
    |
    | Berapa lama audit log disimpan sebelum di-purge.
    | Gunakan artisan command atau PurgeAuditLogAction untuk cleanup.
    | Set null untuk disable auto-purge.
    |
    */

    'retention_days' => env('AUDIT_LOG_RETENTION_DAYS', 90),

];
