<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Services\PropertyData\RealtyUsClient;
use App\Services\PropertyData\ZillowClient;

class PropertyController extends Controller
{
    public function __construct(private ?RealtyUsClient $realty = null, private ?ZillowClient $zillow = null)
    {
        if ($this->realty === null && class_exists(\App\Services\PropertyData\RealtyUsClient::class)) {
            try { $this->realty = app(\App\Services\PropertyData\RealtyUsClient::class); } catch (\Throwable $e) {}
        }
        if ($this->zillow === null && class_exists(\App\Services\PropertyData\ZillowClient::class)) {
            try { $this->zillow = app(\App\Services\PropertyData\ZillowClient::class); } catch (\Throwable $e) {}
        }
    }
    public function index()
    {
        $properties = Property::with('lead')->latest()->get();
        $leads = Lead::orderBy('owner_name')->get(['id', 'owner_name']);
        return view('properties.index', compact('properties', 'leads'));
    }

    // Returns JSON for an address (used by UI auto-fill)
    public function lookup(Request $request)
    {
        $request->validate(['address' => 'required|string|max:255']);
        $api = $this->fetchApiFor($request->address);
        return response()->json($api);
    }

    // Fetch & Save from list (creates new record)
    public function store(Request $request)
    {
        $data = $request->validate([
            'address' => 'required|string|max:255',
            'lead_id' => 'nullable|exists:leads,id',
            'notes' => 'nullable|string',
        ]);

        $api = $this->fetchApiFor($data['address']);

        $payload = [
            'address' => $data['address'],
            'beds' => $api['beds'] ?? null,
            'baths' => $api['baths'] ?? null,
            'kitchens' => $api['kitchens'] ?? null,
            'sqft' => $api['sqft'] ?? null,
            'arv' => $api['arv'] ?? null,
            'estimated_repairs' => $api['estimated_repairs'] ?? null,
            'estimated_rent' => $api['estimated_rent'] ?? null,
            'acquisition_price' => $api['acquisition_price'] ?? ($api['price'] ?? null),
            'sale_price' => $api['sale_price'] ?? null,
            'lead_id' => $data['lead_id'] ?? null,
            'notes' => $data['notes'] ?? null,
            'team_id' => auth()->user()?->team_id,
            'raw_api' => $api['raw_api'] ?? $api,
        ];

        Property::updateOrCreate(['address' => $data['address']], $payload);

        return back()->with('ok', 'Saved!');
    }

    // Edit form
    public function edit(Property $property)
    {
        return redirect()->to('/admin/properties/'.$property->id.'/edit');
    }

    // Update (allows all model fields)
    public function update(Request $request, Property $property)
    {
        $fillable = (new Property())->getFillable();

        $validated = $request->validate(collect($fillable)->mapWithKeys(function ($f) {
            return [$f => 'nullable'];
        })->toArray());

        $property->update($validated);

        return redirect()->route('properties.index')->with('ok', 'Updated!');
    }

    // Diagnostics: verify API wiring
    public function diagnostics(\App\Services\PropertyData\RealtyUsClient $client)
    {
        return response()->json([
            'realtyus' => [
                'key_present' => (bool) config('realtyus.key'),
                'host' => config('realtyus.host'),
                'base_uri' => config('realtyus.base_uri'),
                'probe' => $client->probe(),
            ],
            'zillow' => [
                'key_present' => (bool) config('zillow.key'),
                'host' => config('zillow.host'),
                'base_uri' => config('zillow.base_uri'),
                'probe' => app(\App\Services\PropertyData\ZillowClient::class)->probe(),
            ],
        ]);
    }

    // Returns the raw API payload (no normalization), useful to inspect provider output
    public function raw(Request $request)
    {
        $request->validate(['address' => 'required|string']);
        try {
            if ($this->zillow && $this->zillow->isEnabled()) {
                $raw = $this->zillow->lookupByInput($request->address);
            } elseif ($this->realty && $this->realty->isEnabled()) {
                $raw = $this->realty->lookupByAddress($request->address);
            } else {
                $raw = [];
            }
            return response()->json(['raw' => $raw]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function fetchApiFor(string $address): array
    {
        // 1) Zillow first (per requirement)
        try {
            if ($this->zillow && method_exists($this->zillow, 'isEnabled') && $this->zillow->isEnabled()) {
                $raw = $this->zillow->lookupByInput($address);
                if (!empty($raw)) {
                    $norm = $this->normalizeApi($raw, $address);
                    $norm['raw_api'] = $raw;
                    return $norm;
                }
            }
        } catch (\Throwable $e) { /* continue with fallback */ }

        // 2) RealtyUS as fallback (if configured)
        try {
            if ($this->realty && method_exists($this->realty, 'isEnabled') && $this->realty->isEnabled()) {
                $raw = $this->realty->lookupByAddress($address);
                if (!empty($raw)) {
                    $norm = $this->normalizeApi($raw, $address);
                    $norm['raw_api'] = $raw;
                    return $norm;
                }
            }
        } catch (\Throwable $e) { /* fall through to mock */ }

        return $this->mockApiFor($address);
    }

    // MOCK payload - used as fallback
    private function mockApiFor(string $address): array
    {
        return [
            'beds' => 3,
            'baths' => 2,
            'kitchens' => 1,
            'sqft' => 1450,
            'arv' => 250000.00,
            'estimated_repairs' => 15000,
            'estimated_rent' => 1800,
            'acquisition_price' => 220000.00,
            'sale_price' => null,
            'type' => 'sfr',
        ];
    }

    private function normalizeApi(array $raw, string $address): array
    {
        $searchAny = function (array $data, array $keys): mixed {
            $it = function ($arr) use (&$it, $keys) {
                foreach ($arr as $k => $v) {
                    if (is_string($k) && in_array(strtolower($k), array_map('strtolower', $keys), true)) {
                        if ($v !== null && $v !== '') return $v;
                    }
                    if (is_array($v)) {
                        $found = $it($v);
                        if ($found !== null && $found !== '') return $found;
                    }
                }
                return null;
            };
            return $it($data);
        };

        $get = function ($paths, $default = null, $fallbackKeys = []) use ($raw, $searchAny) {
            foreach ((array) $paths as $p) {
                $v = data_get($raw, $p);
                if (!is_null($v)) return $v;
            }
            if ($fallbackKeys) {
                $f = $searchAny($raw, (array) $fallbackKeys);
                if (!is_null($f)) return $f;
            }
            return $default;
        };

        return [
            'zpid' => $get(['zpid'], null, ['zpid','property_id','id']),
            'address' => $address,
            'beds' => $get(['beds','bedrooms','property.beds','listing.beds'], null, ['beds','bedrooms']),
            'baths' => $get(['baths','bathrooms','property.baths','listing.baths'], null, ['baths','bathrooms']),
            'kitchens' => $get(['kitchens','property.kitchens'], null, ['kitchens']),
            'sqft' => $get(['sqft','area','livingArea','property.area','property.living_area'], null, ['sqft','area','livingsize','living_area','livingareavalue']),
            'arv' => $get(['valuation.estimate','zestimate','valuation.value'], null, ['estimate','estimated_value']),
            'estimated_repairs' => $get(['valuation.repairs','valuation.estimated_repairs'], null, ['estimated_repairs']),
            'estimated_rent' => $get(['rent.estimate','rent.zestimate'], null, ['estimated_rent']),
            'acquisition_price' => $get(['price','list_price','listing.price','valuation.price'], null, ['price','listprice','list_price','amount']),
            'sale_price' => $get(['sale.price','sold_price'], null, ['sale_price','sold_price']),
            'type' => $get(['property.type','type','homeType'], null, ['property_type','home_type','type']),
        ];
    }
}
