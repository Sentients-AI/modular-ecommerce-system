<?php

declare(strict_types=1);

namespace App\Domain\Order\Actions;

use App\Domain\Cart\Exceptions\EmptyCartException;
use App\Domain\Cart\Models\Cart;
use App\Domain\Inventory\Actions\ReserveStock;
use App\Domain\Inventory\DTOs\ReserveStockData;
use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Domain\Inventory\Models\Stock;
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
     * INVARIANT: Stock reservation must be validated BEFORE order creation
     * to prevent split-brain scenarios where order exists without reserved stock.
     *
     * @throws Exception
     */
    public function execute(CreateOrderData $data): Order
    {
        return DB::transaction(function () use ($data) {
            $cart = Cart::query()
                ->with('items.product')
                ->findOrFail($data->cartId);

            // Guard: Cannot create order from an already completed cart
            // Must check BEFORE isEmpty() since completed carts have items deleted
            $cart->assertNotCompleted();

            if ($cart->isEmpty()) {
                throw new EmptyCartException('Cannot create order from empty cart');
            }

            // STEP 1: Lock and validate ALL stock BEFORE creating anything
            // This prevents creating an order when stock is insufficient
            $stockRecords = $this->lockAndValidateStock($cart);

            // Calculate totals
            $subtotalCents = $cart->subtotal;

            $taxCents = $this->calculateTax->execute(
                new TaxCalculationData((int) $subtotalCents)
            );

            $shippingCents = 1000;

            $totalCents = $subtotalCents + $taxCents + $shippingCents;

            // STEP 2: Create order AFTER stock validation passes
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

            // STEP 3: Create order items and reserve stock (already validated)
            foreach ($cart->items as $cartItem) {
                $order->items()->create([
                    'product_id' => $cartItem->product_id,
                    'price_cents_snapshot' => $cartItem->price_cents_snapshot,
                    'tax_cents_snapshot' => $cartItem->tax_cents_snapshot,
                    'quantity' => $cartItem->quantity,
                ]);

                // Reserve stock - we already validated availability
                $this->reserveStock->execute(new ReserveStockData(
                    productId: $cartItem->product_id,
                    quantity: $cartItem->quantity,
                    orderId: $order->id,
                ));
            }

            // STEP 4: Archive the cart (mark as completed)
            $cart->markAsCompleted();

            return $order->fresh(['items']);
        });
    }

    /**
     * Lock and validate all stock records for cart items.
     * Throws InsufficientStockException if any item has insufficient stock.
     *
     * @return array<int, Stock>
     */
    private function lockAndValidateStock(Cart $cart): array
    {
        $stockRecords = [];

        foreach ($cart->items as $cartItem) {
            $stock = Stock::query()
                ->where('product_id', $cartItem->product_id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $stock->isAvailable($cartItem->quantity)) {
                throw new InsufficientStockException(
                    productId: $cartItem->product_id,
                    requested: $cartItem->quantity,
                    available: $stock->quantity_available
                );
            }

            $stockRecords[$cartItem->product_id] = $stock;
        }

        return $stockRecords;
    }
}
