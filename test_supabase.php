<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $service = app(\App\Services\Chat\SupabaseClient::class);
    
    echo "Testing Connection...\n";
    $response = $service->get('conversations', ['limit' => 1]);
    
    if ($response->successful()) {
        echo "SUCCESS! Connected to Supabase and fetched conversations.\n";
        print_r($response->json());
    } else {
        echo "FAILED: HTTP " . $response->status() . "\n";
        echo $response->body() . "\n";
    }
} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
