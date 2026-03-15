<?php

namespace App\Notifications;

use App\Models\UptimeCheck;
use App\Models\Website;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SiteDownNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Website $website,
        public UptimeCheck $check,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()                          // red header in Laravel's default mail
            ->subject("🔴 {$this->website->name} is down")
            ->greeting('Your site is not responding.')
            ->line("**{$this->website->name}** ({$this->website->url}) failed its uptime check.")
            ->line($this->failureDetail())
            ->line("Checked at: {$this->check->checked_at->format('d M Y, H:i:s')} UTC")
            ->action('Go to Dashboard', url('/dashboard'))
            ->line('You will receive another alert if the site goes down again after recovering.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

    private function failureDetail(): string
    {
        if ($this->check->status_code) {
            return "HTTP status returned: **{$this->check->status_code}**";
        }

        if ($this->check->failure_reason) {
            return "Reason: {$this->check->failure_reason}";
        }

        return 'No response received.';
    }
}
