# Plan: Receipt/Evidence Upload for Offline Payments

## Context

At `/checkout/:eventId/:orderShortId/payment` (step 2 – "Payment"), when the buyer
selects **Offline** payment, they currently only see the organizer's
`offline_payment_instructions` and the order is transitioned to
`AWAITING_OFFLINE_PAYMENT`. There is **no way for a buyer to upload a receipt or
proof-of-payment image**. This plan adds that capability end-to-end.

## Critical constraint

The checkout flow is **public/unauthenticated** (uses `publicApi`; the offline
handler verifies the buyer via `CheckoutSessionManagementService`, not a login
token). The existing image endpoint `POST /images` (`CreateImageAction`) lives
inside the **authenticated** route group and calls
`$this->getAuthenticatedUser()`. A public buyer cannot use it. Therefore we must
add a **public** receipt-upload endpoint (or fold the upload into the offline
transition request). This is the most important design point.

## Backend

### 1. Migration
`database/migrations/2026_xx_xx_add_payment_receipt_to_orders_table.php`
(follow `2025_01_19_042750_add_notes_to_orders_table.php`):
```php
Schema::table('orders', static function (Blueprint $table) {
    $table->text('payment_receipt_url')->nullable();
});
```

### 2. Domain object
`app/DomainObjects/Generated/OrderDomainObjectAbstract.php`:
```php
final public const PAYMENT_RECEIPT_URL = 'payment_receipt_url';
```
`app/DomainObjects/OrderDomainObject.php`: add getter/setter following the
`notes` pattern.

### 3. New public image type
`app/DomainObjects/Enums/ImageType.php`:
```php
case ORDER_PAYMENT_RECEIPT;
```
In `getEntityType()` add a branch returning `OrderDomainObject::class`
(add `use HiEvents\DomainObjects\OrderDomainObject;`).

### 4. New public upload action + handler
Add to the **public** group (where `await-offline-payment` lives,
`routes/api.php` ~line 511):
```php
$router->post('/events/{event_id}/order/{order_short_id}/payment-receipt', UploadOrderPaymentReceiptPublicAction::class);
```
The handler should:
- resolve the order via `findByShortId` and verify the session (mirror
  `TransitionOrderToOfflinePaymentHandler::handle` lines 42–55),
- resolve `accountId` from the order's event,
- call the existing `ImageUploadService->upload(
     image, entityId: $order->getId(), entityType: OrderDomainObject::class,
     imageType: ImageType::ORDER_PAYMENT_RECEIPT->name, accountId)` directly —
  bypassing `CreateImageHandler::validateEntityBelongsToUser` (it only checks
  Event/Organizer and passes for Order),
- return the stored URL.

### 5. Extend the offline transition to persist the URL
- `app/Http/Actions/Orders/Public/TransitionOrderToOfflinePaymentPublicAction.php`:
  read `$request->input('payment_receipt_url')`, pass into the DTO.
- `app/Services/Application/Handlers/Order/DTO/TransitionOrderToOfflinePaymentPublicDTO.php`:
  add `public readonly ?string $paymentReceiptUrl = null`.
- `app/Services/Application/Handlers/Order/TransitionOrderToOfflinePaymentHandler.php`:
  in `updateOrderStatuses()` add
  `OrderDomainObjectAbstract::PAYMENT_RECEIPT_URL => $dto->paymentReceiptUrl`.

### 6. Expose it
`app/Resources/Order/OrderResourcePublic.php`: add
`'payment_receipt_url' => $this->getPaymentReceiptUrl()`.

## Frontend

### 7. Types — `src/types.ts`
- Add `'ORDER_PAYMENT_RECEIPT'` to the `ImageType` union (line 185).
- Add `payment_receipt_url?: string;` to the `Order` interface (near line 638).

### 8. New public image upload client — `src/api/image.client.ts`
Add `publicUploadImage` that uses `publicApi` (not `api`) and posts
`multipart/form-data` to the new public route. (Existing `useUploadImage` /
`imageClient.uploadImage` uses the authenticated `api` client and will not work
for buyers.)

### 9. Upload UI — `src/components/routes/product-widget/Payment/PaymentMethods/Offline/index.tsx`
Currently only renders `offline_payment_instructions`. Add an upload control:
- Option A: reuse `ImageUploadDropzone` but point it at the **public** upload
  (requires letting the dropzone accept a custom upload handler), or
- Option B (simpler): add a local Mantine `Dropzone`/`FileButton` that calls the
  new public upload and stores the returned URL in component state.
- Lift the uploaded URL up to `Payment/index.tsx` (e.g. `onReceiptChange`
  callback / `receiptUrl` prop) so the parent can include it when calling
  `transitionOrderToOfflinePayment`.

### 10. Wire into submit
- `src/api/order.client.ts`: `transitionToOfflinePayment(eventId, orderShortId, paymentReceiptUrl?)`
  -> `POST .../await-offline-payment` with the URL.
- `src/mutations/useTransitionOrderToOfflinePaymentPublic.ts`: forward the URL.
- `Payment/index.tsx` `handleSubmit()`: pass `order?.id` as `entityId` for the
  upload and the returned URL into the mutation.

### 11. (Optional) Display
Show the receipt on the checkout summary (`/checkout/.../summary`) and on the
organizer order detail view so staff can verify payment.

## Files touched (summary)

| Layer | File | Change |
|---|---|---|
| DB | `database/migrations/*_add_payment_receipt_to_orders_table.php` | new migration |
| Domain | `OrderDomainObjectAbstract.php`, `OrderDomainObject.php` | new column const + getter/setter |
| Enum | `ImageType.php` | new `ORDER_PAYMENT_RECEIPT` case |
| Route | `routes/api.php` | new public `payment-receipt` route |
| Action/Handler | `UploadOrderPaymentReceiptPublicAction.php` (+ new handler) | public upload, session-verified |
| Offline flow | `TransitionOrderToOfflinePaymentPublicAction.php`, `…PublicDTO.php`, `TransitionOrderToOfflinePaymentHandler.php` | accept + persist URL |
| Resource | `OrderResourcePublic.php` | expose `payment_receipt_url` |
| FE types | `src/types.ts` | `ImageType` + `Order` additions |
| FE api | `src/api/image.client.ts`, `src/api/order.client.ts`, `useTransitionOrderToOfflinePaymentPublic.ts` | public upload + mutation payload |
| FE UI | `Payment/PaymentMethods/Offline/index.tsx`, `Payment/index.tsx` | upload field + wire-up |

## Verification
1. `php artisan migrate`.
2. Hit the public `payment-receipt` endpoint with a session-verified order to
   confirm an unauthenticated buyer can upload.
3. Call `await-offline-payment` with the returned URL and confirm it is
   persisted and returned by `OrderResourcePublic`.
4. Manually walk the checkout offline flow in the browser and confirm the
   uploaded receipt shows on the summary page.
