<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use App\Services\PlatformMailService;
use Google\Client as GoogleClient;
use Illuminate\Http\Request;

class PlatformSettingsController extends Controller
{
    public function index()
    {
        $gmailEmail     = PlatformSetting::get('platform_gmail_email');
        $gmailConnected = (bool) PlatformSetting::get('platform_gmail_access_token');
        $mailer         = app(PlatformMailService::class);

        return view('superadmin.platform.settings', compact('gmailEmail', 'gmailConnected', 'mailer'));
    }

    // Redirect to Google OAuth for gmail.send scope
    public function connectGmail()
    {
        $client = $this->googleClient();
        $client->addScope(\Google\Service\Gmail::GMAIL_SEND);
        $client->addScope('email');
        $client->addScope('profile');
        $client->setAccessType('offline');
        $client->setPrompt('consent'); // force refresh token

        session(['platform_gmail_oauth' => true]);

        return redirect($client->createAuthUrl());
    }

    // Google OAuth callback — stores tokens in platform_settings
    public function gmailCallback(Request $request)
    {
        if (!session('platform_gmail_oauth')) {
            return redirect()->route('superadmin.platform.settings')
                ->withErrors(['error' => 'OAuth session expired. Please try again.']);
        }
        session()->forget('platform_gmail_oauth');

        $code = $request->query('code');
        if (!$code) {
            return redirect()->route('superadmin.platform.settings')
                ->withErrors(['error' => 'Google authorization was cancelled.']);
        }

        try {
            $client = $this->googleClient();
            $token  = $client->fetchAccessTokenWithAuthCode($code);

            if (isset($token['error'])) {
                throw new \RuntimeException($token['error_description'] ?? $token['error']);
            }

            $client->setAccessToken($token);
            $profile = (new \Google\Service\Oauth2($client))->userinfo->get();

            PlatformSetting::set('platform_gmail_access_token',  $token['access_token']);
            PlatformSetting::set('platform_gmail_refresh_token', $token['refresh_token'] ?? PlatformSetting::get('platform_gmail_refresh_token'));
            PlatformSetting::set('platform_gmail_email', $profile->getEmail());
            PlatformSetting::set('platform_gmail_name',  'QuickShul');

            return redirect()->route('superadmin.platform.settings')
                ->with('success', "Platform Gmail connected as {$profile->getEmail()}.");
        } catch (\Throwable $e) {
            \Log::error('Platform Gmail OAuth error: ' . $e->getMessage());
            return redirect()->route('superadmin.platform.settings')
                ->withErrors(['error' => 'Failed to connect Gmail: ' . $e->getMessage()]);
        }
    }

    public function disconnectGmail()
    {
        PlatformSetting::where('key', 'like', 'platform_gmail_%')->delete();
        return back()->with('success', 'Platform Gmail disconnected.');
    }

    // Send a test email
    public function testEmail(Request $request)
    {
        $request->validate(['to' => 'required|email']);

        $mailer  = app(PlatformMailService::class);
        $html    = '<p style="font-family:sans-serif">This is a test email from the QuickShul platform. If you received this, the platform Gmail is working correctly.</p>';
        $success = $mailer->send($request->to, 'QuickShul Platform — Test Email', $html);

        return back()->with(
            $success ? 'success' : 'error',
            $success ? "Test email sent to {$request->to}." : 'Failed to send. Check the logs.'
        );
    }

    private function googleClient(): GoogleClient
    {
        $client = new GoogleClient();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(route('superadmin.platform.gmail.callback'));
        return $client;
    }
}
