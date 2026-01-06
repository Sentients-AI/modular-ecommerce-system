<?php

declare(strict_types=1);

namespace App\Domain\Order\Actions;

use App\Domain\Cart\Exceptions\EmptyCartException;
use App\Domain\Cart\Models\Cart;
use App\Domain\Inventory\Actions\ReserveStockAction;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use Illuminate\Support\Facades\DB;

final readonly class CheckoutAction
{
    public function __construct(
        private ReserveStockAction $reserveStock
    ) {}

    public function execute(Cart $cart): Order
    {
        if ($cart->items->isEmpty()) {
            throw new EmptyCartException();
        }

        return DB::transaction(function () use ($cart) {
            foreach ($cart->items as $item) {
                $this->reserveStock->execute(
                    productId: $item->product_id,
                    quantity: $item->quantity
                );
            }

            $order = Order::create([
                'user_id' => $cart->user_id,
                'status' => OrderStatus::Pending,
                'total' => $cart->total(),
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                ]);
            }

            $cart->items()->delete();

            return $order;
        });
    }
}
