<?php

// config/wati.php

return [
    'endpoint'       => env('WATI_ENDPOINT'),
    'token'          => env('WATI_TOKEN'),
    'otp_template'   => env('WATI_OTP_TEMPLATE', 'otp'),
    'channel_number' => env('WATI_CHANNEL_NUMBER', '919201977476'),
];


// ──────────────────────────────────────────────
// ADD THESE TO YOUR .env FILE
// ──────────────────────────────────────────────
//
// WATI_ENDPOINT=https://live-server-XXXXX.wati.io
// WATI_TOKEN=your_bearer_token_here
// WATI_OTP_TEMPLATE=otp
// WATI_CHANNEL_NUMBER=919201977476
//