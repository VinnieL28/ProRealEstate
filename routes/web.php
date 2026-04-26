<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GmailController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\TeamInvitationController;
use App\Http\Controllers\TwoFactorController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ── Public Marketing Landing Page ──────────────────────────────────────────
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/register', [LandingController::class, 'register'])->name('register.form');
Route::post('/register', [LandingController::class, 'storeRegistration'])
    ->middleware('throttle:5,1')
    ->name('register.store');
Route::view('/terms', 'landing.terms')->name('terms');
Route::view('/privacy', 'landing.privacy')->name('privacy');

Route::middleware(['web', 'auth'])->prefix('admin/backup')->name('backup.')->group(function () {
    Route::get('/full-zip', [\App\Http\Controllers\BackupController::class, 'fullZip'])->name('full-zip');
    Route::get('/leads-excel', [\App\Http\Controllers\BackupController::class, 'leadsExcel'])->name('leads-excel');
    Route::get('/deals-excel', [\App\Http\Controllers\BackupController::class, 'dealsExcel'])->name('deals-excel');
});

// ── Team Invitations ────────────────────────────────────────────────────────
Route::get('/invitation/{token}', [TeamInvitationController::class, 'show'])->name('invitation.accept.show');
Route::post('/invitation/{token}', [TeamInvitationController::class, 'accept'])->name('invitation.accept');

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

// Two-Factor Authentication
Route::get('/2fa/challenge', [TwoFactorController::class, 'challenge'])->name('2fa.challenge')->middleware('guest');
Route::post('/2fa/verify', [TwoFactorController::class, 'verify'])->name('2fa.verify')->middleware(['guest', 'throttle:5,1']);
Route::middleware('auth')->group(function () {
    Route::get('/2fa/setup', [TwoFactorController::class, 'setup'])->name('2fa.setup');
    Route::post('/2fa/enable', [TwoFactorController::class, 'enable'])->name('2fa.enable');
    Route::post('/2fa/disable', [TwoFactorController::class, 'disable'])->name('2fa.disable');
});

// Gmail OAuth & actions (auth required)
Route::middleware('auth')->group(function () {
    Route::get('/gmail/connect', [GmailController::class, 'redirect'])->name('gmail.connect');
    Route::get('/gmail/callback', [GmailController::class, 'callback'])->name('gmail.callback');
    Route::post('/gmail/sync', [GmailController::class, 'sync'])->name('gmail.sync');
    Route::post('/gmail/send', [GmailController::class, 'send'])->name('gmail.send');
});
