<?php

return [
    // Set securely in deployment environment, never commit secrets.
    'email' => env('MAGNOLIA_ADMIN_EMAIL', ''),
    'password_hash' => env('MAGNOLIA_ADMIN_PASSWORD_HASH', ''),
];
