<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminLoginInvite extends Notification
{
    public function __construct(
        private readonly string $loginUrl,
        private readonly string $tenantName,
    ) {}

    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Your {$this->tenantName} Admin Portal Access")
            ->greeting("Hello {$notifiable->name},")
            ->line("A QuickShul administrator has sent you a login link for the {$this->tenantName} member portal.")
            ->action('Log In to Admin Panel', $this->loginUrl)
            ->line('This link expires in 60 minutes.')
            ->line('If you were not expecting this, no action is needed.');
    }
}
