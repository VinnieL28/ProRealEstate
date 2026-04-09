<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::whereIn('key', [
            'ZILLOW_API_KEY', 'ZILLOW_API_HOST', 'ZILLOW_BASE_URI',
            'REALTYUS_API_KEY', 'REALTYUS_API_HOST', 'REALTYUS_BASE_URI',
        ])->pluck('value','key')->toArray();

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'ZILLOW_API_KEY' => 'nullable|string',
            'ZILLOW_API_HOST' => 'nullable|string',
            'ZILLOW_BASE_URI' => 'nullable|string',
            'REALTYUS_API_KEY' => 'nullable|string',
            'REALTYUS_API_HOST' => 'nullable|string',
            'REALTYUS_BASE_URI' => 'nullable|string',
        ]);

        foreach ($data as $k => $v) {
            Setting::updateOrCreate(['key' => $k], ['value' => $v]);
        }

        return back()->with('ok', 'Settings saved (stored in DB for now).');
    }
}

