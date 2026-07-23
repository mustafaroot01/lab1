<?php

namespace App\Services\Chat;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;

class SupabaseClient
{
    private string $baseUrl;
    private string $serviceKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.supabase.url'), '/') . '/rest/v1';
        $this->serviceKey = config('services.supabase.service_role_key');
    }

    /**
     * Get a configured HTTP client for Supabase
     */
    private function client(): PendingRequest
    {
        return Http::withHeaders([
            'apikey' => $this->serviceKey,
            'Authorization' => 'Bearer ' . $this->serviceKey,
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation'
        ])->timeout(10);
    }

    /**
     * Perform a GET request
     */
    public function get(string $table, array $query = [])
    {
        return $this->client()->get("{$this->baseUrl}/{$table}", $query);
    }

    /**
     * Perform a POST request (Insert)
     */
    public function post(string $table, array $data)
    {
        return $this->client()->post("{$this->baseUrl}/{$table}", $data);
    }

    /**
     * Perform a PATCH request (Update)
     */
    public function patch(string $table, array $data, array $query = [])
    {
        $queryString = empty($query) ? '' : '?' . http_build_query($query);
        return $this->client()->patch("{$this->baseUrl}/{$table}{$queryString}", $data);
    }

    /**
     * Perform a DELETE request
     */
    public function delete(string $table, array $query = [])
    {
        $queryString = empty($query) ? '' : '?' . http_build_query($query);
        return $this->client()->delete("{$this->baseUrl}/{$table}{$queryString}");
    }
}
