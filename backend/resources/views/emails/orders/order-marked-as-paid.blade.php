@php use Carbon\Carbon; use HiEvents\Helper\Currency; use HiEvents\Helper\DateHelper; @endphp
@php /** @var \HiEvents\DomainObjects\EventDomainObject $event */ @endphp
@php /** @var \HiEvents\DomainObjects\EventSettingDomainObject $eventSettings */ @endphp
@php /** @var \HiEvents\DomainObjects\OrganizerDomainObject $organizer */ @endphp
@php /** @var \HiEvents\DomainObjects\OrderDomainObject $order */ @endphp

@php /** @see \HiEvents\Mail\Order\OrderMarkedAsPaid */ @endphp

<x-mail::message>
# {{ __('Your Payment Has Been Confirmed!') }} ✅

{{ __('Great news! Your payment for :eventTitle has been confirmed and your order is now complete.', ['eventTitle' => $event->getTitle()]) }}

{{-- Order Reference --}}
<div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 20px; margin: 24px 0; text-align: center;">
    <div style="font-size: 13px; color: #166534; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">{{ __('Order Reference') }}</div>
    <div style="font-size: 28px; font-weight: 700; color: #14532d; letter-spacing: 0.02em;">{{ $order->getPublicId() }}</div>
    <div style="font-size: 14px; color: #16a34a; font-weight: 500; margin-top: 8px;">✓ {{ __('Payment Received') }}</div>
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
<div style="font-size: 15px; font-weight: 500; color: #16a34a;">{{ __('Payment Received') }}</div>
</td>
</tr>
</table>

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

@if($eventSettings->getPostCheckoutMessage())
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
<tr>
<td style="padding: 14px 0 0 0; font-size: 16px; font-weight: 700; color: #0f172a;">{{ __('Total') }}</td>
<td style="padding: 14px 0 0 0; font-size: 16px; font-weight: 700; color: #0f172a; text-align: right;">{{ Currency::format($order->getTotalGross(), $event->getCurrency()) }}</td>
</tr>
</table>
</div>

<x-mail::button :url="$orderUrl">
    {{ __('View Order Summary & Tickets') }}
</x-mail::button>

{{ __('If you have any questions or need assistance, please contact') }} <a href="mailto:{{ $eventSettings->getSupportEmail() }}">{{ $eventSettings->getSupportEmail() }}</a>.

{{ __('Best regards,') }}<br>
{{ $organizer->getName() ?: config('app.name') }}
</x-mail::message>
