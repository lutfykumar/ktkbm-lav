<?php

return [
    'secret' => env('JWT_SECRET', 'tKbM_4p1_s3cR3t_k3Y_2026!@#xQ9zW7vB5mN3pL1'),
    'algorithm' => env('JWT_ALGO', 'HS256'),
    'access_expiry' => (int) env('JWT_ACCESS_EXPIRY', 3600),
    'refresh_expiry' => (int) env('JWT_REFRESH_EXPIRY', 2592000), // 30 days
    'issuer' => env('JWT_ISSUER', 'tkbm-api'),
];
