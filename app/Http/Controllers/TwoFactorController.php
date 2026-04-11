<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PragmaRX\Google2FALaravel\Support\Authenticator;

class TwoFactorController extends Controller
{
    /**
     * Show the 2FA setup page (QR code + secret).
     */
    public function setup(Request $request)
    {
        $user = $request->user();
        $google2fa = app('pragmarx.google2fa');

        if (!$user->two_factor_secret) {
            $user->two_factor_secret = $google2fa->generateSecretKey();
            $user->saveQuietly();
        }

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->two_factor_secret
        );

        $qrCode = \BaconQrCode\Renderer\ImageRenderer::class;

        // Use inline SVG QR code via bacon/bacon-qr-code
        $writer = new \BaconQrCode\Writer(
            new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            )
        );
        $qrCodeSvg = base64_encode($writer->writeString($qrCodeUrl));

        return view('auth.two-factor-setup', [
            'secret' => $user->two_factor_secret,
            'qrCodeSvg' => $qrCodeSvg,
        ]);
    }

    /**
     * Enable 2FA after the user confirms a valid OTP.
     */
    public function enable(Request $request)
    {
        $request->validate(['otp' => 'required|string|digits:6']);

        $user = $request->user();
        $google2fa = app('pragmarx.google2fa');

        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->otp);

        if (!$valid) {
            return back()->withErrors(['otp' => 'Invalid code. Please try again.']);
        }

        $user->update([
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
        ]);

        return redirect()->route('admin')->with('success', '2FA enabled successfully.');
    }

    /**
     * Disable 2FA for the current user.
     */
    public function disable(Request $request)
    {
        $request->user()->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ]);

        return redirect()->route('admin')->with('success', '2FA has been disabled.');
    }

    /**
     * Show the OTP challenge during login.
     */
    public function challenge(Request $request)
    {
        if (!$request->session()->has('2fa:user_id')) {
            return redirect()->route('filament.admin.auth.login');
        }

        return view('auth.two-factor-challenge');
    }

    /**
     * Verify the OTP challenge and complete login.
     */
    public function verify(Request $request)
    {
        $request->validate(['otp' => 'required|string|digits:6']);

        $userId = $request->session()->get('2fa:user_id');
        $user = \App\Models\User::findOrFail($userId);
        $google2fa = app('pragmarx.google2fa');

        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->otp);

        if (!$valid) {
            return back()->withErrors(['otp' => 'Invalid code. Please try again.']);
        }

        $request->session()->forget('2fa:user_id');
        auth()->login($user);

        return redirect()->intended('/admin');
    }
}
