<?php

namespace App\Services;

use App\Models\EmailLog;
use App\Models\Setting;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class GmailService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 15]);
    }

    /**
     * Exchange the stored refresh_token for a short-lived access_token.
     */
    public function getAccessToken(Setting $setting): ?string
    {
        if (!$setting->gmail_client_id || !$setting->gmail_client_secret || !$setting->gmail_refresh_token) {
            return null;
        }

        try {
            $response = $this->client->post('https://oauth2.googleapis.com/token', [
                'form_params' => [
                    'client_id'     => $setting->gmail_client_id,
                    'client_secret' => $setting->gmail_client_secret,
                    'refresh_token' => $setting->gmail_refresh_token,
                    'grant_type'    => 'refresh_token',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['access_token'] ?? null;
        } catch (RequestException $e) {
            Log::error('GmailService: failed to refresh access token — ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Pull latest messages from Gmail inbox and store as EmailLog records.
     * Returns the number of new emails imported.
     */
    public function syncInbox(int $teamId, int $maxResults = 20): int
    {
        $setting = Setting::where('team_id', $teamId)->first();
        if (!$setting) {
            return 0;
        }

        $token = $this->getAccessToken($setting);
        if (!$token) {
            return 0;
        }

        try {
            // List message IDs
            $listResponse = $this->client->get('https://gmail.googleapis.com/gmail/v1/users/me/messages', [
                'headers' => ['Authorization' => 'Bearer ' . $token],
                'query'   => ['maxResults' => $maxResults, 'labelIds' => 'INBOX'],
            ]);

            $listData = json_decode($listResponse->getBody()->getContents(), true);
            $messages = $listData['messages'] ?? [];
            $imported = 0;

            foreach ($messages as $msgMeta) {
                $msgId = $msgMeta['id'];

                // Fetch full message
                $msgResponse = $this->client->get(
                    "https://gmail.googleapis.com/gmail/v1/users/me/messages/{$msgId}",
                    [
                        'headers' => ['Authorization' => 'Bearer ' . $token],
                        'query'   => ['format' => 'metadata', 'metadataHeaders' => ['From', 'To', 'Subject', 'Date']],
                    ]
                );

                $msg = json_decode($msgResponse->getBody()->getContents(), true);
                $headers = collect($msg['payload']['headers'] ?? []);

                $subject  = $headers->firstWhere('name', 'Subject')['value'] ?? '(no subject)';
                $from     = $headers->firstWhere('name', 'From')['value'] ?? '';
                $to       = $headers->firstWhere('name', 'To')['value'] ?? '';
                $dateStr  = $headers->firstWhere('name', 'Date')['value'] ?? null;
                $snippet  = $msg['snippet'] ?? '';
                $sentAt   = $dateStr ? \Carbon\Carbon::parse($dateStr) : now();

                // Skip if already imported
                $alreadyExists = EmailLog::where('team_id', $teamId)
                    ->where('meta->gmail_id', $msgId)
                    ->exists();

                if ($alreadyExists) {
                    continue;
                }

                EmailLog::create([
                    'team_id'      => $teamId,
                    'user_id'      => null,
                    'lead_id'      => null,
                    'direction'    => 'inbound',
                    'subject'      => $subject,
                    'from_address' => $from,
                    'to_address'   => $to,
                    'body_preview' => $snippet,
                    'sent_at'      => $sentAt,
                    'meta'         => ['gmail_id' => $msgId, 'thread_id' => $msg['threadId'] ?? null],
                ]);

                $imported++;
            }

            return $imported;
        } catch (RequestException $e) {
            Log::error('GmailService syncInbox failed: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Send an email via Gmail API.
     */
    public function sendEmail(int $teamId, string $to, string $subject, string $body): bool
    {
        $setting = Setting::where('team_id', $teamId)->first();
        if (!$setting) {
            return false;
        }

        $token = $this->getAccessToken($setting);
        if (!$token) {
            return false;
        }

        // Build RFC 2822 message
        $raw = "To: {$to}\r\nSubject: {$subject}\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\n{$body}";
        $encoded = rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');

        try {
            $this->client->post('https://gmail.googleapis.com/gmail/v1/users/me/messages/send', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type'  => 'application/json',
                ],
                'json' => ['raw' => $encoded],
            ]);

            return true;
        } catch (RequestException $e) {
            Log::error('GmailService sendEmail failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Pull Gmail messages matching a specific lead email and link them to the lead.
     * Returns the number of new emails imported.
     */
    public function syncLeadEmails(int $teamId, int $leadId, string $leadEmail): int
    {
        $setting = Setting::where('team_id', $teamId)->first();
        if (!$setting) {
            return 0;
        }

        $token = $this->getAccessToken($setting);
        if (!$token) {
            return 0;
        }

        try {
            $listResponse = $this->client->get('https://gmail.googleapis.com/gmail/v1/users/me/messages', [
                'headers' => ['Authorization' => 'Bearer ' . $token],
                'query'   => [
                    'maxResults' => 30,
                    'q'          => "from:{$leadEmail} OR to:{$leadEmail}",
                ],
            ]);

            $listData = json_decode($listResponse->getBody()->getContents(), true);
            $messages = $listData['messages'] ?? [];
            $imported = 0;

            foreach ($messages as $msgMeta) {
                $msgId = $msgMeta['id'];

                if (EmailLog::where('team_id', $teamId)->where('meta->gmail_id', $msgId)->exists()) {
                    continue;
                }

                $msgResponse = $this->client->get(
                    "https://gmail.googleapis.com/gmail/v1/users/me/messages/{$msgId}",
                    [
                        'headers' => ['Authorization' => 'Bearer ' . $token],
                        'query'   => ['format' => 'metadata', 'metadataHeaders' => ['From', 'To', 'Subject', 'Date']],
                    ]
                );

                $msg     = json_decode($msgResponse->getBody()->getContents(), true);
                $headers = collect($msg['payload']['headers'] ?? []);

                $subject = $headers->firstWhere('name', 'Subject')['value'] ?? '(no subject)';
                $from    = $headers->firstWhere('name', 'From')['value'] ?? '';
                $to      = $headers->firstWhere('name', 'To')['value'] ?? '';
                $dateStr = $headers->firstWhere('name', 'Date')['value'] ?? null;
                $snippet = $msg['snippet'] ?? '';
                $sentAt  = $dateStr ? \Carbon\Carbon::parse($dateStr) : now();

                // Determine direction based on From address
                $direction = str_contains(strtolower($from), strtolower($leadEmail)) ? 'inbound' : 'outbound';

                EmailLog::create([
                    'team_id'      => $teamId,
                    'user_id'      => null,
                    'lead_id'      => $leadId,
                    'direction'    => $direction,
                    'subject'      => $subject,
                    'from_address' => $from,
                    'to_address'   => $to,
                    'body_preview' => $snippet,
                    'sent_at'      => $sentAt,
                    'meta'         => ['gmail_id' => $msgId, 'thread_id' => $msg['threadId'] ?? null],
                ]);

                $imported++;
            }

            return $imported;
        } catch (RequestException $e) {
            Log::error('GmailService syncLeadEmails failed: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Build the Google OAuth2 authorization URL.
     */
    public function getAuthUrl(Setting $setting, string $redirectUri): string
    {
        $params = http_build_query([
            'client_id'             => $setting->gmail_client_id,
            'redirect_uri'          => $redirectUri,
            'response_type'         => 'code',
            'scope'                 => 'https://www.googleapis.com/auth/gmail.modify',
            'access_type'           => 'offline',
            'prompt'                => 'consent',
        ]);

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . $params;
    }

    /**
     * Exchange an authorization code for tokens and store the refresh_token in Setting.
     */
    public function handleCallback(Setting $setting, string $code, string $redirectUri): bool
    {
        try {
            $response = $this->client->post('https://oauth2.googleapis.com/token', [
                'form_params' => [
                    'code'          => $code,
                    'client_id'     => $setting->gmail_client_id,
                    'client_secret' => $setting->gmail_client_secret,
                    'redirect_uri'  => $redirectUri,
                    'grant_type'    => 'authorization_code',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (!empty($data['refresh_token'])) {
                $setting->update(['gmail_refresh_token' => $data['refresh_token']]);
                return true;
            }

            return false;
        } catch (RequestException $e) {
            Log::error('GmailService handleCallback failed: ' . $e->getMessage());
            return false;
        }
    }
}
