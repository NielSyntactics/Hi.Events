@php use HiEvents\Helper\Currency @endphp

@php /** @uses /backend/app/Mail/OrderSummary.php */ @endphp
@php /** @var \HiEvents\DomainObjects\OrderDomainObject $order */ @endphp
@php /** @var \HiEvents\DomainObjects\EventDomainObject $event */ @endphp

<x-mail::message>
# {{ __('You\'ve received a new order!') }} 🎉

<br>
{{ __('Congratulations! You\'ve received a new order for ') }} <b>{{ $event->getTitle() }}</b>! {{ __('Please find the details below.') }}
<br>
<br>

@if($order->isOrderAwaitingOfflinePayment())
<div style="border-radius: 4px; background-color: #f8d7da; color: #842029; margin-bottom: 1.5rem; padding: 1rem;">
<p>
{{ __('ℹ️ This order is pending payment. Please mark the payment as received on the order management page once payment is received.') }}
</p>
</div>
@endif

@if($order->getPaymentReceiptUrl())
<div style="border-radius: 4px; background-color: #eff6ff; color: #1e40af; margin-bottom: 1.5rem; padding: 1rem;">
<h3 style="margin-top: 0;">{{ __('Payment Receipt') }}</h3>
<p style="margin-bottom: 0.75rem;">{{ __('The buyer has uploaded a payment receipt.') }}</p>
<img src="{{ $order->getPaymentReceiptUrl() }}" alt="{{ __('Payment Receipt') }}" style="max-width: 100%; height: auto; border-radius: 6px; border: 1px solid #bfdbfe; display: block;"/>
</div>
@endif

{{ __('Name') }}: <b>{{ $order->getFullName() }}</b><br>
{{ __('Email') }}: <b>{{ $order->getEmail() }}</b><br>
{{ __('Order Amount:') }} <b>{{ Currency::format($order->getTotalGross(), $event->getCurrency()) }}</b><br>
{{ __('Order ID:') }} <b>{{ $order->getPublicId() }}</b><br>
{{ __('Order Status:') }} <b>{{ $order->getHumanReadableStatus() }}</b>
<br>

<x-mail::button :url="$orderUrl">
    {{ __('View Order') }}
</x-mail::button>

</x-mail::message>






