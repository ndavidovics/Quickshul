<?php
namespace App\Services;

use App\Models\Tenant;
use Google\Client as GoogleClient;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;

class GmailService
{
    protected Tenant $tenant;
    protected GoogleClient $client;

    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
        $this->client = new GoogleClient();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setAccessType('offline');

        if ($tenant->gmail_access_token) {
            $this->client->setAccessToken([
                'access_token'  => $tenant->gmail_access_token,
                'refresh_token' => $tenant->gmail_refresh_token,
                'expires_in'    => $tenant->gmail_token_expires_at
                    ? now()->diffInSeconds($tenant->gmail_token_expires_at, false)
                    : 0,
            ]);

            // Auto-refresh if expired
            if ($this->client->isAccessTokenExpired() && $tenant->gmail_refresh_token) {
                $this->client->fetchAccessTokenWithRefreshToken($tenant->gmail_refresh_token);
                $newToken = $this->client->getAccessToken();
                $tenant->update([
                    'gmail_access_token'     => $newToken['access_token'],
                    'gmail_token_expires_at' => now()->addSeconds($newToken['expires_in'] ?? 3600),
                ]);
            }
        }
    }

    public function isConnected(): bool
    {
        return !empty($this->tenant->gmail_access_token);
    }

    public function send(string $to, string $subject, string $htmlBody, array $cc = []): bool
    {
        if (!$this->isConnected()) {
            \Log::error("GmailService: no Gmail connection for tenant {$this->tenant->slug}");
            return false;
        }

        try {
            $gmail = new Gmail($this->client);

            $fromName  = $this->tenant->name;
            $fromEmail = $this->tenant->gmail_email;

            $headers = "From: {$fromName} <{$fromEmail}>\r\n";
            $headers .= "To: {$to}\r\n";
            if ($cc) {
                $headers .= "Cc: " . implode(', ', $cc) . "\r\n";
            }
            $headers .= "Subject: {$subject}\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            $raw = $headers . "\r\n" . $htmlBody;
            $encoded = rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');

            $message = new Message();
            $message->setRaw($encoded);

            $gmail->users_messages->send('me', $message);
            return true;
        } catch (\Throwable $e) {
            \Log::error("GmailService::send failed for tenant {$this->tenant->slug}: " . $e->getMessage());
            return false;
        }
    }
}
