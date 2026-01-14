<?php

declare(strict_types=1);

use App\Domain\Idempotency\Models\IdempotencyKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('identifies expired keys', function () {
    $expiredKey = IdempotencyKey::factory()->create([
        'expires_at' => now()->subHour(),
    ]);

    $validKey = IdempotencyKey::factory()->create([
        'expires_at' => now()->addHour(),
    ]);

    expect($expiredKey->isExpired())->toBeTrue();
    expect($validKey->isExpired())->toBeFalse();
});

it('identifies valid keys', function () {
    $expiredKey = IdempotencyKey::factory()->create([
        'expires_at' => now()->subHour(),
    ]);

    $validKey = IdempotencyKey::factory()->create([
        'expires_at' => now()->addHour(),
    ]);

    expect($expiredKey->isValid())->toBeFalse();
    expect($validKey->isValid())->toBeTrue();
});

it('casts attributes correctly', function () {
    $key = IdempotencyKey::factory()->create([
        'response_code' => 200,
        'response_body' => ['data' => 'test'],
        'expires_at' => now()->addDay(),
    ]);

    expect($key->response_code)->toBeInt();
    expect($key->response_body)->toBeArray();
    expect($key->expires_at)->not->toBeNull();
});
