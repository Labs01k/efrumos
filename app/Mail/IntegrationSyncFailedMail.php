<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Epic 0 / 0.4 — sent by SubmitOrderToIntegrationLayerJob::failed() once
 * retries are exhausted. The "ответственный сотрудник получает уведомление"
 * acceptance criterion.
 */
class IntegrationSyncFailedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly int $ordersId,
        public readonly string $error,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Заказ #{$this->ordersId} не синхронизирован с 1С/Bitrix24",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.integration.sync-failed');
    }
}
