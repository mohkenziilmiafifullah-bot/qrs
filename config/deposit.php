<?php

return [
    // Shown to Sales on the top-up screen: "Kirim ke <provider> <number> a.n. <name>"
    'destination' => [
        'provider' => env('DEPOSIT_PROVIDER', 'DANA'),
        'number' => env('DEPOSIT_NUMBER'),
        'account_name' => env('DEPOSIT_ACCOUNT_NAME'),
    ],
];
