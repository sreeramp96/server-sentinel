<?php

namespace App\Notifications;

use App\Models\Incident;
use App\Models\Website;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SiteRecoveredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Website $website,
        public Incident $incident,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->success()
            ->subject("✅ {$this->website->name} is back up")
            ->greeting('Good news — your site recovered.')
            ->line("**{$this->website->name}** ({$this->website->url}) is responding normally again.")
            ->line("Downtime duration: **{$this->incident->durationLabel()}**")
            ->line("Recovered at: {$this->incident->resolved_at->format('d M Y, H:i:s')} UTC")
            ->action('View Dashboard', url('/dashboard'))
            ->line('No further action needed.');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
