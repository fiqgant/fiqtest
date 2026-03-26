<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DatabaseBackupMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $filePath,
        public string $filename,
        public string $database,
        public string $generatedAt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[{$this->database}] Database Backup – {$this->generatedAt}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.database-backup',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->filePath)
                ->as($this->filename)
                ->withMime('application/sql'),
        ];
    }
}
