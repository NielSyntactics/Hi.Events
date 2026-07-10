@php use Carbon\Carbon; use HiEvents\Helper\Currency; use HiEvents\Helper\DateHelper; @endphp
@php /** @var \HiEvents\DomainObjects\OrderDomainObject $order */ @endphp
@php /** @var \HiEvents\DomainObjects\EventDomainObject $event */ @endphp
@php /** @var \HiEvents\DomainObjects\OrganizerDomainObject $organizer */ @endphp
@php /** @var \HiEvents\DomainObjects\EventSettingDomainObject $eventSettings */ @endphp

@php /** @see \HiEvents\Mail\Order\OrderSummary */ @endphp

<x-mail::message>
@if($order->isOrderAwaitingOfflinePayment() === false)
# {{ __('Your Order is Confirmed!') }} 🎉

{{ __('Congratulations! Your order for :eventTitle was successful. Please find your order details below.', ['eventTitle' => $event->getTitle()]) }}
@else
# {{ __('Order Received — Awaiting Payment') }} ⏳

{{ __('Your order has been placed. Tickets have been issued but will not be valid until payment is received.') }}
@endif

{{-- Order Reference --}}
<div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin: 24px 0; text-align: center;">
    <div style="font-size: 13px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">{{ __('Order Reference') }}</div>
    <div style="font-size: 28px; font-weight: 700; color: #0f172a; letter-spacing: 0.02em;">{{ $order->getPublicId() }}</div>
</div>

{{-- Order Details --}}
<table width="100%" cellpadding="0" cellspacing="0" style="margin: 24px 0;">
<tr>
<td width="50%" style="padding-right: 12px; vertical-align: top;">
<div style="font-size: 13px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">{{ __('Name') }}</div>
<div style="font-size: 15px; color: #0f172a; font-weight: 500;">{{ $order->getFullName() }}</div>
</td>
<td width="50%" style="padding-left: 12px; vertical-align: top;">
<div style="font-size: 13px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">{{ __('Email') }}</div>
<div style="font-size: 15px; color: #0f172a; font-weight: 500;">{{ $order->getEmail() }}</div>
</td>
</tr>
<tr>
<td width="50%" style="padding-right: 12px; padding-top: 16px; vertical-align: top;">
<div style="font-size: 13px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">{{ __('Order Date') }}</div>
<div style="font-size: 15px; color: #0f172a; font-weight: 500;">{{ (new Carbon($order->getCreatedAt()))->format('F j, Y \a\t g:i A') }}</div>
</td>
<td width="50%" style="padding-left: 12px; padding-top: 16px; vertical-align: top;">
<div style="font-size: 13px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">{{ __('Payment Status') }}</div>
<div style="font-size: 15px; font-weight: 500; @if($order->isOrderAwaitingOfflinePayment()) color: #d97706; @else color: #16a34a; @endif">{{ $order->isOrderAwaitingOfflinePayment() ? __('Awaiting Offline Payment') : __('Payment Received') }}</div>
</td>
</tr>
</table>

@if($order->isOrderAwaitingOfflinePayment())
<div style="background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 16px 20px; margin: 24px 0;">
    <div style="font-size: 15px; font-weight: 600; color: #92400e; margin-bottom: 8px;">{{ __('Payment Instructions') }}</div>
    <div style="font-size: 14px; color: #78350f;">{!! $eventSettings->getOfflinePaymentInstructions() !!}</div>
</div>
@endif

{{-- Event Details --}}
<div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin: 24px 0;">
<div style="font-size: 17px; font-weight: 600; color: #0f172a; margin-bottom: 16px;">{{ __('Event Details') }}</div>

<table width="100%" cellpadding="0" cellspacing="0">
<tr>
<td width="50%" style="padding-right: 12px; vertical-align: top;">
<div style="font-size: 13px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">{{ __('Event Name') }}</div>
<div style="font-size: 15px; color: #0f172a; font-weight: 500;">{{ $event->getTitle() }}</div>
</td>
<td width="50%" style="padding-left: 12px; vertical-align: top;">
<div style="font-size: 13px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">{{ __('Date & Time') }}</div>
<div style="font-size: 15px; color: #0f172a; font-weight: 500;">{{ (new Carbon(DateHelper::convertFromUTC($event->getStartDate(), $event->getTimezone())))->format('F j, Y \a\t g:i A') }}</div>
</td>
</tr>
<tr>
<td width="50%" style="padding-right: 12px; padding-top: 16px; vertical-align: top;">
<div style="font-size: 13px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">{{ __('Timezone') }}</div>
<div style="font-size: 15px; color: #0f172a; font-weight: 500;">{{ $event->getTimezone() }}</div>
</td>
<td width="50%" style="padding-left: 12px; padding-top: 16px; vertical-align: top;">
<div style="font-size: 13px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">{{ __('Organizer') }}</div>
<div style="font-size: 15px; color: #0f172a; font-weight: 500;">{{ $organizer->getName() ?: config('app.name') }}</div>
</td>
</tr>
</table>
</div>

@if($eventSettings->getPostCheckoutMessage() && $order->isOrderCompleted())
<div style="background-color: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 16px 20px; margin: 24px 0;">
    <div style="font-size: 15px; font-weight: 600; color: #0c4a6e; margin-bottom: 8px;">{{ __('Additional Information') }}</div>
    <div style="font-size: 14px; color: #075985;">{!! $eventSettings->getPostCheckoutMessage() !!}</div>
</div>
@endif

{{-- Order Summary --}}
<div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin: 24px 0;">
<div style="font-size: 17px; font-weight: 600; color: #0f172a; margin-bottom: 16px;">{{ __('Order Summary') }}</div>

<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
<tr style="border-bottom: 1px solid #e2e8f0;">
<td style="padding: 10px 0; font-size: 14px; color: #64748b;">{{ __('Order Number') }}</td>
<td style="padding: 10px 0; font-size: 14px; color: #0f172a; font-weight: 500; text-align: right;">{{ $order->getPublicId() }}</td>
</tr>
<tr style="border-bottom: 1px solid #e2e8f0;">
<td style="padding: 10px 0; font-size: 14px; color: #64748b;">{{ __('Subtotal') }}</td>
<td style="padding: 10px 0; font-size: 14px; color: #0f172a; text-align: right;">{{ Currency::format($order->getTotalBeforeAdditions(), $event->getCurrency()) }}</td>
</tr>
@if($order->getTotalTax() > 0)
<tr style="border-bottom: 1px solid #e2e8f0;">
<td style="padding: 10px 0; font-size: 14px; color: #64748b;">{{ __('Tax') }}</td>
<td style="padding: 10px 0; font-size: 14px; color: #0f172a; text-align: right;">{{ Currency::format($order->getTotalTax(), $event->getCurrency()) }}</td>
</tr>
@endif
@if($order->getTotalFee() > 0)
<tr style="border-bottom: 1px solid #e2e8f0;">
<td style="padding: 10px 0; font-size: 14px; color: #64748b;">{{ __('Fees') }}</td>
<td style="padding: 10px 0; font-size: 14px; color: #0f172a; text-align: right;">{{ Currency::format($order->getTotalFee(), $event->getCurrency()) }}</td>
</tr>
@endif
@if($order->getTotalRefunded() > 0)
<tr style="border-bottom: 1px solid #e2e8f0;">
<td style="padding: 10px 0; font-size: 14px; color: #64748b;">{{ __('Refunded') }}</td>
<td style="padding: 10px 0; font-size: 14px; color: #dc2626; text-align: right;">-{{ Currency::format($order->getTotalRefunded(), $event->getCurrency()) }}</td>
</tr>
@endif
<tr>
<td style="padding: 14px 0 0 0; font-size: 16px; font-weight: 700; color: #0f172a;">{{ __('Total') }}</td>
<td style="padding: 14px 0 0 0; font-size: 16px; font-weight: 700; color: #0f172a; text-align: right;">{{ Currency::format($order->getTotalGross(), $event->getCurrency()) }}</td>
</tr>
</table>
</div>

{{ __('If you have any questions or need assistance, please contact') }} <a href="mailto:{{ $organizer->getEmail() }}">{{ $organizer->getEmail() }}</a>.

{{ __('Best regards,') }}<br>
{{ $organizer->getName() ?: config('app.name') }}
</x-mail::message>
