<?php

declare(strict_types=1);

namespace App\Application\UseCases\Refund;

use App\Application\DTOs\Request\RequestRefundRequest;
use App\Application\DTOs\Response\RefundResponse;
use App\Domain\Order\Models\Order;
use App\Domain\Order\Specifications\OrderIsRefundable;
use App\Domain\Refund\Actions\RequestRefundAction;
use App\Domain\Refund\Specifications\RefundAmountIsValid;

final readonly class RequestRefundUseCase
{
    public function __construct(
        private RequestRefundAction $requestRefundAction,
    ) {}

    public function execute(RequestRefundRequest $request): RefundResponse
    {
        $order = Order::query()
            ->with('paymentIntent')
            ->findOrFail($request->orderId->toInt());

        // Validate using composed specifications
        $orderSpec = (new OrderIsRefundable())
            ->and(new RefundAmountIsValid($request->amountCents));

        $orderSpec->assertSatisfiedBy($order);

        // Execute domain action
        $refund = $this->requestRefundAction->execute(
            order: $order,
            amountCents: $request->amountCents,
            reason: $request->reason,
        );

        return RefundResponse::fromRefund($refund);
    }
}
