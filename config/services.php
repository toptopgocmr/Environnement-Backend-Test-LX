<?php

return [

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme'   => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // ─── Peex (agrégateur mobile money — collecte) ────────────────────────────
    'peex' => [
        'base_url'          => env('PEEX_BASE_URL', 'https://sandbox.peexit.com/api/v1'),
        'secret_key'        => env('PEEX_SECRET_KEY'),
        'callback_username' => env('PEEX_CALLBACK_USERNAME', 'peex'),
        'callback_password' => env('PEEX_CALLBACK_PASSWORD'),
        // Codes pays ISO Alpha-2 où Peex peut collecter, ex: "CM,CG"
        'collect_countries' => array_values(array_filter(array_map(
            'trim', explode(',', strtoupper(env('PEEX_COLLECT_COUNTRIES', 'CM,CG')))
        ))),
    ],

    // ─── Stripe ───────────────────────────────────────────────────────────────
    'stripe' => [
        'key'    => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    // ─── SMS (Africa's Talking) ────────────────────────────────────────────────
    'sms' => [
        'username' => env('AT_USERNAME', 'sandbox'),
        'api_key'  => env('AT_API_KEY'),
        'url'      => env('AT_URL', 'https://api.africastalking.com/version1/messaging'),
    ],

    // ─── AWS S3 ───────────────────────────────────────────────────────────────
    'aws' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        'bucket' => env('AWS_BUCKET', 'lirex-storage'),
    ],

    // ─── Anthropic ───────────────────────────────────────────────────────────
    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model'   => env('ANTHROPIC_MODEL', 'claude-opus-4-6'),
    ],

];
