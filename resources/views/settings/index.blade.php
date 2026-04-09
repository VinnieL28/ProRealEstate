@php($title = 'Settings')
@extends('layouts.app')
@section('content')
<div class="card">
    <h2 style="margin-top:0">Settings</h2>
    <p class="muted">These values are stored in the database for the app UI. For production, you should also set the corresponding .env keys.</p>
    <form method="POST" action="{{ route('settings.update') }}" class="grid" style="max-width:900px">
        @csrf
        <div class="row">
            <div>
                <label>Zillow API Key</label>
                <input type="text" name="ZILLOW_API_KEY" value="{{ $settings['ZILLOW_API_KEY'] ?? '' }}">
            </div>
            <div>
                <label>Zillow Host</label>
                <input type="text" name="ZILLOW_API_HOST" value="{{ $settings['ZILLOW_API_HOST'] ?? 'zillow-com1.p.rapidapi.com' }}">
            </div>
        </div>
        <div class="row">
            <div>
                <label>Zillow Base URI</label>
                <input type="text" name="ZILLOW_BASE_URI" value="{{ $settings['ZILLOW_BASE_URI'] ?? 'https://zillow-com1.p.rapidapi.com/' }}">
            </div>
        </div>

        <div class="row">
            <div>
                <label>RealtyUS API Key</label>
                <input type="text" name="REALTYUS_API_KEY" value="{{ $settings['REALTYUS_API_KEY'] ?? '' }}">
            </div>
            <div>
                <label>RealtyUS Host</label>
                <input type="text" name="REALTYUS_API_HOST" value="{{ $settings['REALTYUS_API_HOST'] ?? 'realty-us.p.rapidapi.com' }}">
            </div>
        </div>
        <div class="row">
            <div>
                <label>RealtyUS Base URI</label>
                <input type="text" name="REALTYUS_BASE_URI" value="{{ $settings['REALTYUS_BASE_URI'] ?? 'https://realty-us.p.rapidapi.com/' }}">
            </div>
        </div>

        <div>
            <button class="btn" type="submit">Save</button>
        </div>
    </form>
</div>
@endsection

