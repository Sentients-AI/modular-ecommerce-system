<?php

declare(strict_types=1);

namespace App\Domain\Order\Actions;

use App\Domain\Cart\Exceptions\EmptyCartException;
use App\Domain\Cart\Models\Cart;
use App\Domain\Inventory\Actions\ReserveStock;
use App\Domain\Inventory\DTOs\ReserveStockData;
use App\Domain\Order\DTOs\CreateOrderData;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Tax\Actions\CalculateTax;
use App\Domain\Tax\DTOs\TaxCalculationData;
use Exception;
use Illuminate\Support\Facades\DB;

final readonly class CreateOrderFromCart
{
    public function __construct(
        private ReserveStock $reserveStock,
        private CalculateTax $calculateTax,
    ) {}

    /**
     * Execute the action to create an order from a cart.
     *
     * @throws Exception
     */
    public function execute(CreateOrderData $data): Order
    {
        return DB::transaction(function () use ($data) {
            $cart = Cart::query()
                ->with('items.product')
                ->findOrFail($data->cartId);

            if ($cart->isEmpty()) {
                throw new EmptyCartException('Cannot create order from empty cart');
            }

            // Calculate totals
            $subtotalCents = $cart->subtotal;

            $taxCents = $this->calculateTax->execute(
                new TaxCalculationData((int) $subtotalCents)
            );

            $shippingCents = 1000;

            $totalCents = $subtotalCents + $taxCents + $shippingCents;

            // Create order
            $order = Order::query()->create([
                'user_id' => $data->userId,
                'order_number' => Order::generateOrderNumber(),
                'status' => OrderStatus::Pending,
                'subtotal_cents' => $subtotalCents,
                'tax_cents' => $taxCents,
                'shipping_cost_cents' => $shippingCents,
                'total_cents' => $totalCents,
                'currency' => $data->currency,
            ]);

            // Create order items and reserve stock
            foreach ($cart->items as $cartItem) {
                $order->items()->create([
                    'product_id' => $cartItem->product_id,
                    'price_cents_snapshot' => $cartItem->price_cents_snapshot,
                    'tax_cents_snapshot' => $cartItem->tax_cents_snapshot,
                    'quantity' => $cartItem->quantity,
                ]);

                // Reserve stock for this order
                $this->reserveStock->execute(new ReserveStockData(
                    productId: $cartItem->product_id,
                    quantity: $cartItem->quantity,
                    orderId: $order->id,
                ));
            }

            // Clear the cart
            $cart->items()->delete();

            return $order->fresh(['items', 'payment']);
        });
    }
}
