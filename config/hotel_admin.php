<?php

return [
    // Set securely in deployment environment, never commit secrets.
    'username' => env('MAGNOLIA_ADMIN_USERNAME', 'admin'),
    'password_hash' => env('MAGNOLIA_ADMIN_PASSWORD_HASH', ''),
];
