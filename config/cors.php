<?php

// قائمة الدومينات المسموح بها تُضبط عبر CORS_ALLOWED_ORIGINS (مفصولة بفواصل).
// في التطوير تبقى مفتوحة (*) إن لم تُحدَّد، وفي الإنتاج يجب ضبطها بدومينات محددة.
$allowedOrigins = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('CORS_ALLOWED_ORIGINS', '*'))
)));

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => $allowedOrigins ?: ['*'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
