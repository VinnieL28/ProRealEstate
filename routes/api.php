<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InboundResponseController;
use App\Http\Controllers\Api\LeadIngestionController;
use App\Http\Controllers\Api\TwilioWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/leads/ingest', [LeadIngestionController::class, 'store']);
Route::post('/drip/inbound-response', [InboundResponseController::class, 'store']);

// Twilio webhooks (no CSRF, no auth — Twilio signs requests)
Route::post('/twilio/sms/inbound', [TwilioWebhookController::class, 'inboundSms'])->name('twilio.sms.inbound');
Route::post('/twilio/call/status', [TwilioWebhookController::class, 'callStatus'])->name('twilio.call.status');
