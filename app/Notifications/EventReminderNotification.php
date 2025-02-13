<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class EventReminderNotification extends Notification implements ShouldQueue // ✅ Implements Queueing
{
    use Queueable;

    public function __construct(public Event $event)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail']; // ✅ Can add 'database' if needed
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Reminder: {$this->event->name} is starting soon!")
            ->greeting("Hello, {$notifiable->name}!")
            ->line("You have an upcoming event: **{$this->event->name}**.")
            ->line("🗓 **Date & Time:** " . Carbon::parse($this->event->start_time)->format('l, F j, Y \a\t g:i A'))
            ->action('View Event', url('/events/' . $this->event->id))
            ->line('Thank you for using our platform!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'event_id' => $this->event->id,
            'event_name' => $this->event->name,
            'event_start_time' => Carbon::parse($this->event->start_time)->toDateTimeString(),
        ];
    }
}
