<?php

namespace App\Http\Controllers;

use App\Jobs\SyncGmailJob;
use App\Models\Setting;
use App\Services\GmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GmailController extends Controller
{
    public function __construct(private GmailService $gmail) {}

    /**
     * Redirect to Google OAuth2 consent screen.
     */
    public function redirect(): RedirectResponse
    {
        $setting = Setting::where('team_id', auth()->user()->team_id)->first();

        if (!$setting?->gmail_client_id) {
            return redirect('/admin/settings')->with('error', 'Configure Gmail Client ID in Settings first.');
        }

        $url = $this->gmail->getAuthUrl($setting, route('gmail.callback'));
        return redirect($url);
    }

    /**
     * Handle OAuth2 callback from Google, store refresh_token.
     */
    public function callback(Request $request): RedirectResponse
    {
        $code = $request->query('code');
        if (!$code) {
            return redirect('/admin/gmail')->with('error', 'No authorization code returned by Google.');
        }

        $setting = Setting::where('team_id', auth()->user()->team_id)->first();

        $success = $this->gmail->handleCallback($setting, $code, route('gmail.callback'));

        if ($success) {
            return redirect('/admin/gmail')->with('success', 'Gmail connected successfully!');
        }

        return redirect('/admin/gmail')->with('error', 'Failed to connect Gmail. Check credentials.');
    }

    /**
     * Trigger a manual inbox sync.
     */
    public function sync(): RedirectResponse
    {
        $teamId = auth()->user()->team_id;
        SyncGmailJob::dispatch($teamId);

        return redirect('/admin/gmail')->with('success', 'Gmail sync queued. Emails will appear shortly.');
    }

    /**
     * Send an email via Gmail API.
     */
    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'to'      => 'required|email',
            'subject' => 'required|string|max:255',
            'body'    => 'required|string',
        ]);

        $teamId = auth()->user()->team_id;
        $sent   = $this->gmail->sendEmail($teamId, $data['to'], $data['subject'], $data['body']);

        if ($sent) {
            return redirect('/admin/gmail')->with('success', 'Email sent successfully.');
        }

        return redirect('/admin/gmail')->with('error', 'Failed to send email. Check Gmail credentials.');
    }
}
