<?php

return [
    'company' => [
        'name' => env('ORDERFLOW_COMPANY_NAME', 'OrderFlow'),
        'legal_name' => env('ORDERFLOW_COMPANY_LEGAL_NAME', 'OrderFlow'),
        'support_email' => env('ORDERFLOW_SUPPORT_EMAIL', env('MAIL_FROM_ADDRESS', 'support@orderflow.id')),
        'support_phone' => env('ORDERFLOW_SUPPORT_PHONE'),
        'billing_phone' => env('ORDERFLOW_BILLING_PHONE', env('ORDERFLOW_SUPPORT_PHONE')),
    ],

    'legal' => [
        'terms_version' => env('ORDERFLOW_TERMS_VERSION', '2026-09-20'),
        'privacy_version' => env('ORDERFLOW_PRIVACY_VERSION', '2026-09-20'),
    ],

    'billing' => [
        'accounts' => [
            [
                'code' => 'bca',
                'bank' => 'Bank BCA',
                'number' => env('ORDERFLOW_BCA_ACCOUNT'),
                'holder' => env('ORDERFLOW_BCA_ACCOUNT_HOLDER'),
            ],
            [
                'code' => 'mandiri',
                'bank' => 'Bank Mandiri',
                'number' => env('ORDERFLOW_MANDIRI_ACCOUNT'),
                'holder' => env('ORDERFLOW_MANDIRI_ACCOUNT_HOLDER'),
            ],
        ],
        'qris_enabled' => env('ORDERFLOW_QRIS_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Private file storage
    |--------------------------------------------------------------------------
    | Design files and payment proofs are never written to the public web root.
    | They are streamed only through authorized controllers. "legacy_disk" is
    | read as a fallback for files uploaded before private storage existed.
    */
    'storage' => [
        'private_disk' => env('ORDERFLOW_PRIVATE_DISK', 'local'),
        'legacy_disk' => 'public',
    ],

    'backup' => [
        'path' => env('ORDERFLOW_BACKUP_PATH', storage_path('app/backups')),
        'retention_days' => (int) env('ORDERFLOW_BACKUP_RETENTION_DAYS', 14),
        'include_files' => (bool) env('ORDERFLOW_BACKUP_INCLUDE_FILES', true),
        'max_age_hours' => (int) env('ORDERFLOW_BACKUP_MAX_AGE_HOURS', 26),
    ],

    'health' => [
        // When set, requests sending header "X-Health-Token" receive detailed checks.
        'token' => env('ORDERFLOW_HEALTH_TOKEN'),
    ],

    'security' => [
        'force_https' => (bool) env('ORDERFLOW_FORCE_HTTPS', false),
        'hsts_max_age' => (int) env('ORDERFLOW_HSTS_MAX_AGE', 31536000),
    ],
];
