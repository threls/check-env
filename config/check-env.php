<?php

// config for Threls/ThrelsCheckEnv
return [
    /*
      |--------------------------------------------------------------------------
      | Temporary Environment Suffix
      |--------------------------------------------------------------------------
      |
      | This suffix is used to create temporary environment files for validation.
      | example: .env.staging.test.encrypted
      |
      */
    'temp-env-suffix' => 'test',

    /*
      |--------------------------------------------------------------------------
      | Environment Definitions
      |--------------------------------------------------------------------------
      |
      | Define your environments here. The key should be the environment name,
      | and the encryption-key value should be the env key which will store the encryption key.
      |
      */

    'environments' => [
        'staging' => [
            'encryption-key' => 'ENV_ENCRYPTION_KEY',
        ],

        // Users can add as many environments as they want:
        // 'production' => [
        //     'encryption-key' => '',
        // ],
    ],

    'files' => [
        '.env',
        '.env.example',
        // Users can add as many env files as they want:
    ],

    /* Show values on env diff table */
    'show_values' => false,
];
