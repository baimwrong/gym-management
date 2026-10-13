<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Gym Brand & Identity
    |--------------------------------------------------------------------------
    */
    'name' => env('GYM_NAME', 'PREMIER FITNESS CLUB'),
    'short_name' => env('GYM_SHORT_NAME', 'GYMPRO'),
    'tagline' => env('GYM_TAGLINE', 'Premium Strength, Conditioning & Wellness'),
    'phone' => env('GYM_PHONE', '+1 (555) 389-4450'),
    'email' => env('GYM_EMAIL', 'info@gym.local'),
    'address' => env('GYM_ADDRESS', '742 Fitness Boulevard, Downtown Metro'),
    'city' => env('GYM_CITY', 'Metro City'),
    'postal_code' => env('GYM_POSTAL', '10001'),
    'tax_number' => env('GYM_TAX_NO', 'TAX-US-99238410'),

    /*
    |--------------------------------------------------------------------------
    | Financial & POS Defaults
    |--------------------------------------------------------------------------
    */
    'currency_symbol' => env('GYM_CURRENCY_SYMBOL', '$'),
    'currency_code' => env('GYM_CURRENCY_CODE', 'USD'),
    'tax_rate_percentage' => (float) env('GYM_TAX_RATE', 8.5), // 8.5%
    'invoice_prefix' => env('GYM_INVOICE_PREFIX', 'INV-'),
    'member_prefix' => env('GYM_MEMBER_PREFIX', 'GYM-'),

    /*
    |--------------------------------------------------------------------------
    | Operating Hours & Check-in Rules
    |--------------------------------------------------------------------------
    */
    'operating_hours' => [
        'weekday' => '05:00 AM - 11:00 PM',
        'weekend' => '06:00 AM - 10:00 PM',
    ],
    'allow_grace_period_days' => (int) env('GYM_GRACE_PERIOD_DAYS', 3), // days to enter gym after expiration with warning
    'offline_network_mode' => true,
];
