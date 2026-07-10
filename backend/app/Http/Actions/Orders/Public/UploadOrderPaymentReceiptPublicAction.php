<?php

namespace HiEvents\Http\Actions\Orders\Public;

use HiEvents\DomainObjects\Enums\ImageType;
use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\OrderDomainObject;
use HiEvents\Http\Actions\BaseAction;
use HiEvents\Http\Request\Image\UploadOrderPaymentReceiptRequest;
use HiEvents\Helper\Url;
use HiEvents\Repository\Interfaces\EventRepositoryInterface;
use HiEvents\Repository\Interfaces\OrderRepositoryInterface;
use HiEvents\Services\Domain\Image\ImageUploadService;
use HiEvents\Services\Infrastructure\Session\CheckoutSessionManagementService;
use HiEvents\Exceptions\ResourceConflictException;
use HiEvents\Exceptions\UnauthorizedException;
use Illuminate\Http\JsonResponse;

class UploadOrderPaymentReceiptPublicAction extends BaseAction
{
    public function __construct(
        private readonly OrderRepositoryInterface      $orderRepository,
        private readonly EventRepositoryInterface       $eventRepository,
        private readonly ImageUploadService             $imageUploadService,
        private readonly CheckoutSessionManagementService $sessionManagementService,
    )
    {
    }

    public function __invoke(UploadOrderPaymentReceiptRequest $request, int $eventId, string $orderShortId): JsonResponse
    {
        $order = $this->orderRepository->findByShortId($orderShortId);

        if ($order === null || $order->getEventId() !== $eventId) {
            throw new ResourceConflictException(__('Order not found'));
        }

        if ($order->getSessionId() === null
            || !$this->sessionManagementService->verifySession($order->getSessionId())) {
            throw new UnauthorizedException(
                __('Sorry, we could not verify your session. Please restart your order.')
            );
        }

        /** @var EventDomainObject $event */
        $event = $this->eventRepository->findById($eventId);
        $accountId = $event->getAccountId();

        $image = $this->imageUploadService->upload(
            image: $request->file('image'),
            entityId: $order->getId(),
            entityType: OrderDomainObject::class,
            imageType: ImageType::ORDER_PAYMENT_RECEIPT->name,
            accountId: $accountId,
        );

        $receiptUrl = Url::getCdnUrl($image->getPath());

        return $this->jsonResponse([
            'data' => [
                'url' => $receiptUrl,
            ],
        ]);
    }
}
