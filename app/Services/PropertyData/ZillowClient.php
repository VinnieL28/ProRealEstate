<?php

namespace App\Services\PropertyData;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ZillowClient
{
    private Client $http;

    public function __construct(?Client $client = null)
    {
        $this->http = $client ?: new Client([
            'timeout'  => 12,
        ]);
    }

    public function isEnabled(): bool
    {
        return (bool) config('zillow.key');
    }

    /**
     * Lookup by input. If numeric, treats as zpid.
     * Otherwise tries search endpoints to resolve to a zpid, then fetches detail.
     */
    public function lookupByInput(string $input): array
    {
        $headers = [
            'x-rapidapi-key' => config('zillow.key'),
            'x-rapidapi-host' => config('zillow.host'),
            'accept' => 'application/json',
        ];

        $base = rtrim(config('zillow.base_uri'), '/');

        // If input looks like a zpid, call detail directly
        if (preg_match('/^\d{4,}$/', trim($input))) {
            if ($data = $this->safeGet("$base/property", [ 'zpid' => trim($input) ], $headers)) {
                return $data;
            }
        }

        // Try to resolve zpid via common Zillow search endpoints
        $searches = [
            ['path' => 'propertyExtendedSearch', 'query' => fn($q) => ['location' => $q]],
            ['path' => 'search', 'query' => fn($q) => ['location' => $q]],
            ['path' => 'search', 'query' => fn($q) => ['address' => $q]],
        ];

        foreach ($searches as $s) {
            $url = "$base/{$s['path']}";
            $q = $s['query']($input);
            $res = $this->safeGet($url, $q, $headers);
            $zpid = $this->findFirst($res ?? [], ['zpid','property_id','id']);
            if ($zpid) {
                if ($detail = $this->safeGet("$base/property", ['zpid' => $zpid], $headers)) {
                    return $detail;
                }
            }
        }

        return [];
    }

    private function safeGet(string $url, array $query, array $headers): ?array
    {
        try {
            $res = $this->http->get($url, [ 'query' => $query, 'headers' => $headers ]);
            $json = json_decode((string) $res->getBody(), true);
            return is_array($json) ? $json : null;
        } catch (\Throwable $e) {
            Log::warning('Zillow API request failed', [
                'url' => $url,
                'query' => $query,
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function findFirst(array $data, array $keys): ?string
    {
        $iter = function ($arr) use (&$iter, $keys) {
            foreach ($arr as $k => $v) {
                if (in_array((string) $k, $keys, true) && is_scalar($v)) {
                    return (string) $v;
                }
                if (is_array($v)) {
                    $r = $iter($v);
                    if ($r !== null) return $r;
                }
            }
            return null;
        };
        return $iter($data);
    }

    public function probe(): array
    {
        $base = rtrim(config('zillow.base_uri'), '/');
        $headers = [
            'x-rapidapi-key' => config('zillow.key'),
            'x-rapidapi-host' => config('zillow.host'),
            'accept' => 'application/json',
        ];
        try {
            // Use a known sample zpid for probe (does not need to fill the app)
            $res = $this->http->get("$base/property", [ 'query' => ['zpid' => '197832'], 'headers' => $headers ]);
            return [ 'ok' => $res->getStatusCode() === 200 ];
        } catch (\Throwable $e) {
            return [ 'ok' => false, 'error' => $e->getMessage() ];
        }
    }
}

