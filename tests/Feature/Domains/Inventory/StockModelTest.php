<?php

declare(strict_types=1);

use App\Domain\Inventory\Models\Stock;
use App\Domain\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('belongs to a product', function () {
    $product = Product::factory()->create();
    $stock = Stock::factory()->create(['product_id' => $product->id]);

    expect($stock->product)->toBeInstanceOf(Product::class);
    expect($stock->product->id)->toBe($product->id);
});

it('calculates available quantity correctly', function () {
    $stock = Stock::factory()->create([
        'quantity_available' => 10,
        'quantity_reserved' => 3,
    ]);

    expect($stock->isAvailable(7))->toBeTrue();
    expect($stock->isAvailable(8))->toBeFalse();
});

it('determines if stock is available', function () {
    $stock = Stock::factory()->create([
        'quantity_available' => 5,
        'quantity_reserved' => 0,
    ]);

    expect($stock->isAvailable(5))->toBeTrue();
    expect($stock->isAvailable(6))->toBeFalse();
    expect($stock->isAvailable(0))->toBeTrue();
});

it('casts attributes correctly', function () {
    $stock = Stock::factory()->create([
        'quantity_available' => 10,
        'quantity_reserved' => 3,
    ]);

    expect($stock->quantity_available)->toBeInt();
    expect($stock->quantity_reserved)->toBeInt();
});
