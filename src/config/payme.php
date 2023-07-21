<?php

return [
    'min_amount' => env('PAYME_MIN_AMOUNT', 1_000_00),
    'max_amount' => env('PAYME_MAX_AMOUNT', 100_000_000_00),
    'identity' => env('PAYME_IDENTITY', 'id'),
    'login' => env('PAYME_LOGIN', 'TestUser'),
    'key' => env('PAYME_KEY', 'TestKey'),
    'merchant_id' => env('PAYME_MERCHANT_ID', '123456789'),
    'allowed_ips' => [
        '185.178.51.131', '185.178.51.132', '195.158.31.134', '195.158.31.10', '195.158.28.124', '195.158.5.82', '127.0.0.1'
    ]
];