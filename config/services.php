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

    // ─── MTN Mobile Money ──────────────────────────────────────────────────────
    'mtn_momo' => [
        'base_url'         => env('MTN_MOMO_BASE_URL', 'https://sandbox.momodeveloper.mtn.com'),
        'subscription_key' => env('MTN_MOMO_SUBSCRIPTION_KEY'),
        'api_user'         => env('MTN_MOMO_API_USER'),
        'api_key'          => env('MTN_MOMO_API_KEY'),
        'environment'      => env('MTN_MOMO_ENV', 'sandbox'),
        'currency'         => env('MTN_MOMO_CURRENCY', 'EUR'), // EUR en sandbox, XAF en prod
    ],

    // ─── Airtel Money ─────────────────────────────────────────────────────────
    'airtel' => [
        'base_url'      => env('AIRTEL_BASE_URL', 'https://openapi.airtel.africa'),
        'client_id'     => env('AIRTEL_CLIENT_ID'),
        'client_secret' => env('AIRTEL_CLIENT_SECRET'),
        'country'       => 'CG',
        'currency'      => 'XAF',
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
