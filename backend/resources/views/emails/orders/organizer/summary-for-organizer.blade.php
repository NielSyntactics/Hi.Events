@php use Carbon\Carbon; use HiEvents\Helper\Currency; use HiEvents\Helper\DateHelper; @endphp

@php /** @uses /backend/app/Mail/OrderSummary.php */ @endphp
@php /** @var \HiEvents\DomainObjects\OrderDomainObject $order */ @endphp
@php /** @var \HiEvents\DomainObjects\EventDomainObject $event */ @endphp

<x-mail::message>
# {{ __('New Order Received!') }} 🎉

{{ __('You\'ve received a new order for') }} <strong>{{ $event->getTitle() }}</strong>.

{{-- Order Reference --}}
<div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin: 24px 0; text-align: center;">
    <div style="font-size: 13px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">{{ __('Order Reference') }}</div>
    <div style="font-size: 28px; font-weight: 700; color: #0f172a; letter-spacing: 0.02em;">{{ $order->getPublicId() }}</div>
</div>

@if($order->isOrderAwaitingOfflinePayment())
<div style="background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 16px 20px; margin: 24px 0;">
    <div style="font-size: 14px; color: #92400e; font-weight: 500;">⏳ {{ __('This order is awaiting offline payment. The buyer will upload a receipt once payment is made.') }}</div>
</div>
@endif

{{-- Order Details --}}
<table width="100%" cellpadding="0" cellspacing="0" style="margin: 24px 0;">
<tr>
<td width="50%" style="padding-right: 12px; vertical-align: top;">
<div style="font-size: 13px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">{{ __('Customer Name') }}</div>
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
<div style="font-size: 13px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">{{ __('Order Status') }}</div>
<div style="font-size: 15px; font-weight: 500; @if($order->isOrderAwaitingOfflinePayment()) color: #d97706; @else color: #16a34a; @endif">{{ $order->getHumanReadableStatus() }}</div>
</td>
</tr>
</table>

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

{{ __('Best regards,') }}<br>
{{ config('app.name') }}
</x-mail::message>
