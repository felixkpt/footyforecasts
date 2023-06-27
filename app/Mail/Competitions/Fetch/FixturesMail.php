<?php

namespace App\Mail\Competitions\Fetch;

use Illuminate\Mail\Mailables\Address;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class FixturesMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('admin@footyforecasts.com', 'FootyForeCasts'),
            replyTo: [
                new Address('felixkpt@gmail.com', 'Felix Biwott'),
                new Address('mawefelix@gmail.com', 'Mawe'),
            ],
            subject: ($this->data['is_detailed'] === true ? 'Detailed Competitions Fixtures Fetch Mail' : 'Competitions Fixtures Fetch Mail') . ' >> ' . Carbon::now()->toDayDateTimeString(),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: $this->data['is_detailed'] === true ? 'emails.competitions.fetch.detailed_fixtures' : 'emails.competitions.fetch.fixtures',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
