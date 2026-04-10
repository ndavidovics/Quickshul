<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;

/**
 * Platform-level Gmail sender for root-domain emails (not tenant-scoped).
 * Credentials stored in .env as PLATFORM_GMAIL_*.
 */
class PlatformMailService
{
    protected GoogleClient $client;
    protected bool $connected = false;

    public function __construct()
    {
        // Prefer DB-stored tokens (set via super admin), fall back to .env
        $accessToken  = \App\Models\PlatformSetting::get('platform_gmail_access_token')
                        ?? config('services.platform_gmail.access_token');
        $refreshToken = \App\Models\PlatformSetting::get('platform_gmail_refresh_token')
                        ?? config('services.platform_gmail.refresh_token');

        if (!$accessToken) {
            return;
        }

        $this->client = new GoogleClient();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setAccessType('offline');
        $this->client->setAccessToken([
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in'    => 0, // force refresh check
        ]);

        if ($this->client->isAccessTokenExpired() && $refreshToken) {
            $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
            $new = $this->client->getAccessToken();
            // Persist refreshed token back to env file would require extra work;
            // for now just use in-memory for this request.
        }

        $this->connected = true;
    }

    public function isConnected(): bool
    {
        return $this->connected;
    }

    public function send(string $to, string $subject, string $htmlBody): bool
    {
        if (!$this->connected) {
            \Log::warning('PlatformMailService: no platform Gmail configured — email not sent.');
            return false;
        }

        try {
            $gmail     = new Gmail($this->client);
            $fromName  = \App\Models\PlatformSetting::get('platform_gmail_name')
                         ?? config('services.platform_gmail.name', 'QuickShul');
            $fromEmail = \App\Models\PlatformSetting::get('platform_gmail_email')
                         ?? config('services.platform_gmail.email', 'noreply@quickshul.com');

            $headers  = "From: {$fromName} <{$fromEmail}>\r\n";
            $headers .= "To: {$to}\r\n";
            $headers .= "Subject: {$subject}\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            $raw     = $headers . "\r\n" . $htmlBody;
            $encoded = rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');

            $message = new Message();
            $message->setRaw($encoded);
            $gmail->users_messages->send('me', $message);

            return true;
        } catch (\Throwable $e) {
            \Log::error('PlatformMailService::send failed: ' . $e->getMessage());
            return false;
        }
    }
}
