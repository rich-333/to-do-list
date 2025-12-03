<?php

namespace App\Jobs;

use App\Models\Event;
use App\Mail\EventReminderMail;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * Job to send an event reminder email.
 *
 * @property \App\Models\Event $event
 */
class SendEventReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Event $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function handle(): void
    {
        if (! $this->event->user || empty($this->event->user->email)) {
            return; // no recipient
        }

        Mail::to($this->event->user->email)
            ->send(new EventReminderMail($this->event));
    }
}
