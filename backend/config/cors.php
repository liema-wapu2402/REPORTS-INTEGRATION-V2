<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    // Comma-separated, e.g. "https://app.vercel.app,https://app-git-main-me.vercel.app"
    'allowed_origins' => array_values(array_filter(array_map('trim', explode(',', env('FRONTEND_URL', 'http://localhost:5173'))))),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => ['Content-Disposition'],
    'max_age' => 0,
    'supports_credentials' => false,
];
