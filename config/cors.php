<?php

return [
    // Match the fixed browser address in the Flutter Chrome debug configuration.
    'paths' => ['api/mobile/*'],
    'allowed_methods' => ['GET', 'POST', 'PATCH', 'OPTIONS'],
    'allowed_origins' => ['http://127.0.0.1:7357'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Accept', 'Authorization', 'Content-Type'],
    'exposed_headers' => [],
    'max_age' => 0,
    // The app uses bearer tokens, not browser session cookies.
    'supports_credentials' => false,
];
