<?php

namespace App\Services\PropertyData;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class RealtyUsClient
{
    private Client $http;

    public function __construct(?Client $client = null)
    {
        $this->http = $client ?: new Client([
            'base_uri' => rtrim(config('realtyus.base_uri'), '/') . '/',
            'timeout'  => 10,
        ]);
    }

    public function isEnabled(): bool
    {
        return (bool) config('realtyus.key');
    }

    /**
     * Best‑effort lookup by address.
     * Tries a direct detail endpoint if available; otherwise attempts a search then detail.
     */
    public function lookupByAddress(string $address): array
    {
        $headers = [
            'x-rapidapi-key' => config('realtyus.key'),
            'x-rapidapi-host' => config('realtyus.host'),
            'accept' => 'application/json',
        ];

        // Strategy 1: direct detail endpoint (v3 then v2) with address
        foreach ([
            ['path' => 'properties/v3/detail', 'param' => 'address'],
            ['path' => 'properties/v2/detail', 'param' => 'address'],
            ['path' => 'property/detail', 'param' => 'address'],
        ] as $opt) {
            $resp = $this->safeGet($opt['path'], [ $opt['param'] => $address ], $headers);
            if ($resp) return $resp;
        }

        // Strategy 2: autocomplete/search for property id
        $search = $this->safeGet('properties/v2/auto-complete', [ 'input' => $address ], $headers)
            ?: $this->safeGet('properties/v3/auto-complete', [ 'input' => $address ], $headers)
            ?: $this->safeGet('properties/v2/search', [ 'query' => $address ], $headers)
            ?: $this->safeGet('properties/v3/search', [ 'query' => $address ], $headers);

        $propId = $this->findFirst($search, ['property_id','propertyId','id']);
        if ($propId) {
            foreach ([
                'properties/v3/detail',
                'properties/v2/detail',
                'properties/v2/summary',
            ] as $p) {
                $detail = $this->safeGet($p, [ 'property_id' => $propId ], $headers);
                if ($detail) return $detail;
            }
        }

        // Strategy 3: some APIs accept a generic 'location' param
        $resp = $this->safeGet('properties/v3/detail', [ 'location' => $address ], $headers)
            ?: $this->safeGet('properties/v2/detail', [ 'location' => $address ], $headers);
        if ($resp) return $resp;

        // Last resort: return whatever search returned to aid debugging/normalization
        return $search ?: [];
    }

    private function safeGet(string $path, array $query, array $headers): ?array
    {
        try {
            $url = rtrim(config('realtyus.base_uri'), '/') . '/' . ltrim($path, '/');
            $res = $this->http->get($url, [ 'query' => $query, 'headers' => $headers ]);
            $json = json_decode((string) $res->getBody(), true);
            return is_array($json) ? $json : null;
        } catch (\Throwable $e) {
            Log::warning('RealtyUS API request failed', [
                'path' => $path,
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
                if (in_array((string)$k, $keys, true) && is_scalar($v)) {
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

    /** Lightweight connectivity check (non-critical endpoint). */
    public function probe(): array
    {
        $headers = [
            'x-rapidapi-key' => config('realtyus.key'),
            'x-rapidapi-host' => config('realtyus.host'),
            'accept' => 'application/json',
        ];
        try {
            $url = rtrim(config('realtyus.base_uri'), '/') . '/timezone';
            $res = $this->http->get($url, [ 'query' => [ 'lat' => 47.6062, 'lng' => -122.3321 ], 'headers' => $headers ]);
            return [ 'ok' => $res->getStatusCode() === 200 ];
        } catch (\Throwable $e) {
            return [ 'ok' => false, 'error' => $e->getMessage() ];
        }
    }
}
