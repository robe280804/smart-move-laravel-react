<?php

return [
    'stripe_prices' => [
        'advanced' => env('STRIPE_ADVANCED_PRICE_ID'),
        'pro' => env('STRIPE_PRO_PRICE_ID'),
    ],

    'success_url' => env('STRIPE_CHECKOUT_SUCCESS_URL', env('FRONTEND_URL', 'http://localhost:5173').'/dashboard'),
    'cancel_url' => env('STRIPE_CHECKOUT_CANCEL_URL', env('FRONTEND_URL', 'http://localhost:5173').'/#pricing'),
];
