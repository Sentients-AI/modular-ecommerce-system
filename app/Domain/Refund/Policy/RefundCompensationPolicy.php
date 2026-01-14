<?php

declare(strict_types=1);

namespace App\Domain\Refund\Policy;

use App\Domain\Order\Models\OrderItem;

final class RefundCompensationPolicy
{
    public function shouldReleaseStock(OrderItem $item): bool
    {
        return ! $item->is_digital
            && ! $item->is_perishable;
    }
}
