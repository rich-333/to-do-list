<?php

namespace App\Mail;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable for event reminders
 *
 * @property \App\Models\Event $event
 */
class EventReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Event $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function build(): EventReminderMail
    {
        return $this->subject('Recordatorio: ' . $this->event->titulo)
                    ->view('emails.event_reminder')
                    ->with(['event' => $this->event]);
    }
}
