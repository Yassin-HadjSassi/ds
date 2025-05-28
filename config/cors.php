<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'], // Include your API routes
    'allowed_methods' => ['*'], // Allow all methods (GET, POST, OPTIONS, etc.)
    'allowed_origins' => ['*'], // Your frontend domain
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'], // Allow all headers
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true, // Set to true if using Sanctum or cookies
];