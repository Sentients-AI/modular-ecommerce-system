<?php

declare(strict_types=1);

namespace App\Domain\Cart\Actions;

use App\Domain\Cart\DTOs\CartItemData;
use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartItem;
use App\Domain\Product\Models\Product;
use DomainException;
use Illuminate\Support\Facades\DB;

final class AddItemToCart
{
    /**
     * Execute the action to add an item to the cart.
     *
     * @throws DomainException If cart is already completed
     */
    public function execute(Cart $cart, CartItemData $data): CartItem
    {
        return DB::transaction(function () use ($cart, $data) {
            // Guard: Cannot add items to a completed cart
            $cart->assertNotCompleted();

            $product = Product::query()->findOrFail($data->productId);

            // Use provided price or product's effective price
            $price = $data->price ?? $product->effective_price;

            // Check if item already exists in cart
            $existingItem = $cart->items()
                ->where('product_id', $data->productId)
                ->first();

            if ($existingItem) {
                $existingItem->increment('quantity', $data->quantity);

                return $existingItem->fresh();
            }

            // Create new cart item
            return $cart->items()->create([
                'product_id' => $data->productId,
                'quantity' => $data->quantity,
                'price_cents_snapshot' => $price,
                'tax_cents_snapshot' => (int) ($price * 0.1),
            ]);
        });
    }
}
