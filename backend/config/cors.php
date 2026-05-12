<?php

return [
    'paths' => ['api/*', 'register', 'login'],
    'allowed_methods' => ['GET', 'POST', 'PATCH', 'DELETE'],
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:5173')],
    'allowed_headers' => ['Content-Type', 'Accept', 'Authorization'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
