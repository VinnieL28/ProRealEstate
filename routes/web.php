<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GmailController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Default to Filament Admin dashboard
Route::redirect('/', '/admin')->name('dashboard.index');

// Properties
Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
Route::post('/properties/lookup', [PropertyController::class, 'lookup'])->name('properties.lookup');
Route::get('/properties/lookup', [PropertyController::class, 'lookup']);
Route::get('/properties/diagnostics', [PropertyController::class, 'diagnostics']);
Route::get('/properties/raw', [PropertyController::class, 'raw']);
Route::post('/properties', [PropertyController::class, 'store'])->name('properties.store');
Route::get('/properties/{property}/edit', [PropertyController::class, 'edit'])->name('properties.edit');
Route::put('/properties/{property}', [PropertyController::class, 'update'])->name('properties.update');

// Debug endpoint to verify which project is serving
Route::get('/_whoami', function () {
    return response()->json([
        'base_path' => base_path(),
        'laravel_version' => app()->version(),
        'routes_cached' => app()->routesAreCached(),
        'app_env' => config('app.env'),
        'app_url' => config('app.url'),
    ]);
});

// Leads
Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
Route::get('/leads/active', [LeadController::class, 'active'])->name('leads.active');
Route::get('/leads/warm', [LeadController::class, 'warm'])->name('leads.warm');
Route::get('/leads/create', [LeadController::class, 'create'])->name('leads.create');
Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
Route::get('/leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
Route::put('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
Route::post('/leads/import', [LeadController::class, 'import'])->name('leads.import');

// Communications placeholder
Route::view('/communications', 'communications.index')->name('communications.index');

// Settings
Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

// Gmail OAuth & actions (auth required)
Route::middleware('auth')->group(function () {
    Route::get('/gmail/connect', [GmailController::class, 'redirect'])->name('gmail.connect');
    Route::get('/gmail/callback', [GmailController::class, 'callback'])->name('gmail.callback');
    Route::post('/gmail/sync', [GmailController::class, 'sync'])->name('gmail.sync');
    Route::post('/gmail/send', [GmailController::class, 'send'])->name('gmail.send');
});
