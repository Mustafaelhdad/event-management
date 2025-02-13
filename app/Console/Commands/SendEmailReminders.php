<?php

namespace App\Console\Commands;

namespace App\Console\Commands;

use App\Models\Event;
use App\Notifications\EventReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SendEmailReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-email-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders to attendees for events happening in the next 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fetching events happening in the next 24 hours...');

        // Get events starting in the next 24 hours
        $events = Event::whereBetween('start_time', [now(), now()->addDay()])
            ->with('attendees.user')
            ->get();

        $eventCount = $events->count();
        $eventLabel = Str::plural('event', $eventCount);

        $this->info("Found {$eventCount} {$eventLabel}.");

        if ($events->isEmpty()) {
            $this->info('No upcoming events in the next 24 hours.');
            return;
        }

        foreach ($events as $event) {
            foreach ($event->attendees as $attendee) {
                $user = $attendee->user;

                if ($user) {
                    // ✅ Use Notification instead of Mail::to()
                    $user->notify(new EventReminderNotification($event));

                    $this->info("Reminder sent to {$user->email} for event: {$event->name}");
                }
            }
        }

        $this->info('All reminders have been sent successfully!');
    }
}
