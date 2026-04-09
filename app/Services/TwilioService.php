<?php

namespace App\Services;

use App\Models\Setting;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class TwilioService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 10]);
    }

    private function getSettings(?int $teamId = null): ?Setting
    {
        $teamId = $teamId ?? auth()->user()?->team_id;
        return Setting::where('team_id', $teamId)->first();
    }

    /**
     * Send an outbound SMS via Twilio.
     * Returns true on success, false if credentials missing or API error.
     */
    public function sendSms(string $to, string $body, ?int $teamId = null): bool
    {
        $setting = $this->getSettings($teamId);

        if (!$setting?->twilio_sid || !$setting?->twilio_token || !$setting?->twilio_phone_number) {
            Log::warning('TwilioService: credentials not configured for team ' . $teamId);
            return false;
        }

        $to = $this->normalizePhone($to);
        if (!$to) {
            Log::warning('TwilioService: invalid phone number');
            return false;
        }

        try {
            $response = $this->client->post(
                "https://api.twilio.com/2010-04-01/Accounts/{$setting->twilio_sid}/Messages.json",
                [
                    'auth'        => [$setting->twilio_sid, $setting->twilio_token],
                    'form_params' => [
                        'To'   => $to,
                        'From' => $setting->twilio_phone_number,
                        'Body' => $body,
                    ],
                ]
            );

            $status = $response->getStatusCode();
            if ($status === 201) {
                return true;
            }

            Log::warning('TwilioService: unexpected status ' . $status);
            return false;
        } catch (RequestException $e) {
            Log::error('TwilioService SMS failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Make a voice call via Twilio (initiates a call with TwiML URL).
     */
    public function makeCall(string $to, string $twimlUrl, ?int $teamId = null): bool
    {
        $setting = $this->getSettings($teamId);

        if (!$setting?->twilio_sid || !$setting?->twilio_token || !$setting?->twilio_phone_number) {
            return false;
        }

        $to = $this->normalizePhone($to);
        if (!$to) {
            return false;
        }

        try {
            $response = $this->client->post(
                "https://api.twilio.com/2010-04-01/Accounts/{$setting->twilio_sid}/Calls.json",
                [
                    'auth'        => [$setting->twilio_sid, $setting->twilio_token],
                    'form_params' => [
                        'To'   => $to,
                        'From' => $setting->twilio_phone_number,
                        'Url'  => $twimlUrl,
                    ],
                ]
            );

            return $response->getStatusCode() === 201;
        } catch (RequestException $e) {
            Log::error('TwilioService call failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Normalize a phone number to E.164 format (+1XXXXXXXXXX).
     * Returns null if the number can't be cleaned.
     */
    public function normalizePhone(string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (strlen($digits) === 10) {
            return '+1' . $digits;
        }

        if (strlen($digits) === 11 && $digits[0] === '1') {
            return '+' . $digits;
        }

        if (strlen($digits) > 10) {
            return '+' . $digits;
        }

        return null;
    }
}
