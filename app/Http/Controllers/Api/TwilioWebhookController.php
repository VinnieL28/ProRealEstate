<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Lead;
use App\Models\Setting;
use App\Models\SmsLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TwilioWebhookController extends Controller
{
    /**
     * Handle inbound SMS from Twilio.
     * Twilio sends: Body, From, To, MessageSid, AccountSid, NumMedia, etc.
     */
    public function inboundSms(Request $request): Response
    {
        $from    = $request->input('From');   // e.g. +15551234567
        $to      = $request->input('To');     // your Twilio number
        $body    = $request->input('Body', '');
        $sid     = $request->input('MessageSid');

        // Find the team by matching the Twilio "To" number in settings
        $setting = Setting::where('twilio_phone_number', $to)->first();
        $teamId  = $setting?->team_id;

        // Try to match an existing lead by phone number
        $normalizedFrom = preg_replace('/\D/', '', $from);
        $lead = Lead::where('team_id', $teamId)
            ->where(function ($q) use ($normalizedFrom) {
                $q->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(phone, '-', ''), ' ', ''), '(', ''), ')', '') = ?", [$normalizedFrom])
                  ->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(primary_phone, '-', ''), ' ', ''), '(', ''), ')', '') = ?", [$normalizedFrom]);
            })
            ->first();

        // Log the SMS
        $log = SmsLog::create([
            'team_id'   => $teamId,
            'lead_id'   => $lead?->id,
            'user_id'   => null,
            'sent_at'   => now(),
            'direction' => 'inbound',
            'message'   => $body,
            'notes'     => 'Twilio SID: ' . $sid,
        ]);

        // Log activity on the lead if matched
        if ($lead) {
            Activity::create([
                'team_id'      => $teamId,
                'user_id'      => null,
                'related_type' => 'Lead',
                'related_id'   => $lead->id,
                'type'         => 'sms',
                'description'  => 'Inbound SMS: ' . \Illuminate\Support\Str::limit($body, 200),
                'meta'         => ['twilio_sid' => $sid, 'from' => $from],
            ]);
        }

        // Return empty TwiML response (no auto-reply)
        return response('<?xml version="1.0" encoding="UTF-8"?><Response></Response>', 200)
            ->header('Content-Type', 'text/xml');
    }

    /**
     * Handle Twilio voice call status callbacks.
     */
    public function callStatus(Request $request): Response
    {
        // Accept Twilio status callbacks silently (200 OK)
        return response('', 200);
    }
}
