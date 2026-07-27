<?php

namespace HiEvents\Mail\Organizer;

use Barryvdh\DomPDF\Facade\Pdf;
use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\EventSettingDomainObject;
use HiEvents\DomainObjects\InvoiceDomainObject;
use HiEvents\DomainObjects\OrderDomainObject;
use HiEvents\Helper\Currency;
use HiEvents\Helper\Url;
use HiEvents\Mail\BaseMail;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Str;

/**
 * @uses /backend/resources/views/emails/orders/organizer/order-marked-as-paid-for-organizer.blade.php
 */
class OrderMarkedAsPaidForOrganizer extends BaseMail
{
    public function __construct(
        private readonly OrderDomainObject        $order,
        private readonly EventDomainObject        $event,
        private readonly EventSettingDomainObject $eventSettings,
        private readonly ?InvoiceDomainObject     $invoice = null,
    )
    {
        parent::__construct();
    }

    public function envelope(): Envelope
    {
        $subject = __('An order has been marked as paid for :event 🎉', [
            'event' => Str::limit($this->event->getTitle(), 75),
        ]);

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        $attendeeTicketUrls = [];
        if ($this->order->getAttendees()) {
            foreach ($this->order->getAttendees() as $attendee) {
                $attendeeTicketUrls[$attendee->getId()] = sprintf(
                    Url::getFrontEndUrlFromConfig(Url::ATTENDEE_TICKET),
                    $this->event->getId(),
                    $attendee->getShortId(),
                );
            }
        }

        return new Content(
            markdown: 'emails.orders.organizer.order-marked-as-paid-for-organizer',
            with: [
                'event' => $this->event,
                'order' => $this->order,
                'eventSettings' => $this->eventSettings,
                'attendeeTicketUrls' => $attendeeTicketUrls,
                'orderUrl' => sprintf(
                    Url::getFrontEndUrlFromConfig(Url::ORGANIZER_ORDER_SUMMARY),
                    $this->event->getId(),
                    $this->order->getId(),
                ),
            ]
        );
    }

    public function attachments(): array
    {
        if ($this->invoice === null) {
            return [];
        }

        $invoice = Pdf::loadView('invoice', [
            'order' => $this->order,
            'event' => $this->event,
            'organizer' => $this->event->getOrganizer(),
            'eventSettings' => $this->eventSettings,
            'invoice' => $this->invoice,
        ]);

        return [
            Attachment::fromData(
                static fn() => $invoice->output(),
                'invoice.pdf',
            )->withMime('application/pdf'),
        ];
    }
}
