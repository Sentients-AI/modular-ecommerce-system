<?php

declare(strict_types=1);

use App\Domain\Idempotency\Actions\EnsureIdempotentAction;
use App\Domain\Idempotency\Actions\StoreIdempotencyResultAction;
use App\Domain\Idempotency\Models\IdempotencyKey;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

describe('EnsureIdempotentAction', function () {
    it('returns null when no existing record', function () {
        $user = User::factory()->create();

        $action = app(EnsureIdempotentAction::class);
        $result = $action->execute(
            key: 'test_key',
            userId: $user->id,
            action: 'checkout',
            payload: ['item_id' => 1]
        );

        expect($result)->toBeNull();
    });

    it('returns cached response for matching payload', function () {
        $user = User::factory()->create();
        $payload = ['item_id' => 1];
        $fingerprint = hash('sha256', json_encode($payload));

        IdempotencyKey::create([
            'key' => 'test_key',
            'user_id' => $user->id,
            'action' => 'checkout',
            'request_fingerprint' => $fingerprint,
            'response_code' => 200,
            'response_body' => ['order_id' => 123],
            'expires_at' => now()->addDay(),
        ]);

        $action = app(EnsureIdempotentAction::class);
        $result = $action->execute(
            key: 'test_key',
            userId: $user->id,
            action: 'checkout',
            payload: $payload
        );

        expect($result)->toBe(['order_id' => 123]);
    });

    it('throws conflict exception for different payload', function () {
        $user = User::factory()->create();
        $originalPayload = ['item_id' => 1];
        $fingerprint = hash('sha256', json_encode($originalPayload));

        IdempotencyKey::create([
            'key' => 'test_key',
            'user_id' => $user->id,
            'action' => 'checkout',
            'request_fingerprint' => $fingerprint,
            'response_code' => 200,
            'response_body' => ['order_id' => 123],
            'expires_at' => now()->addDay(),
        ]);

        $action = app(EnsureIdempotentAction::class);
        $action->execute(
            key: 'test_key',
            userId: $user->id,
            action: 'checkout',
            payload: ['item_id' => 2]
        );
    })->throws(ConflictHttpException::class, 'Idempotency key reused with different payload');

    it('deletes expired record and returns null', function () {
        $user = User::factory()->create();
        $payload = ['item_id' => 1];
        $fingerprint = hash('sha256', json_encode($payload));

        IdempotencyKey::create([
            'key' => 'test_key',
            'user_id' => $user->id,
            'action' => 'checkout',
            'request_fingerprint' => $fingerprint,
            'response_code' => 200,
            'response_body' => ['order_id' => 123],
            'expires_at' => now()->subHour(),
        ]);

        $action = app(EnsureIdempotentAction::class);
        $result = $action->execute(
            key: 'test_key',
            userId: $user->id,
            action: 'checkout',
            payload: $payload
        );

        expect($result)->toBeNull();
        expect(IdempotencyKey::count())->toBe(0);
    });
});

describe('StoreIdempotencyResultAction', function () {
    it('stores idempotency result', function () {
        $user = User::factory()->create();
        $payload = ['item_id' => 1];

        $action = app(StoreIdempotencyResultAction::class);
        $result = $action->execute(
            key: 'test_key',
            userId: $user->id,
            action: 'checkout',
            payload: $payload,
            responseCode: 201,
            responseBody: ['order_id' => 456]
        );

        expect($result)->toBeInstanceOf(IdempotencyKey::class);
        expect($result->key)->toBe('test_key');
        expect($result->user_id)->toBe($user->id);
        expect($result->action)->toBe('checkout');
        expect($result->response_code)->toBe(201);
        expect($result->response_body)->toBe(['order_id' => 456]);
        expect($result->request_fingerprint)->toBe(hash('sha256', json_encode($payload)));
    });

    it('returns existing record for duplicate key', function () {
        $user = User::factory()->create();
        $payload = ['item_id' => 1];
        $fingerprint = hash('sha256', json_encode($payload));

        $existing = IdempotencyKey::create([
            'key' => 'existing_key',
            'user_id' => $user->id,
            'action' => 'checkout',
            'request_fingerprint' => $fingerprint,
            'response_code' => 200,
            'response_body' => ['order_id' => 123],
            'expires_at' => now()->addDay(),
        ]);

        $action = app(StoreIdempotencyResultAction::class);
        $result = $action->execute(
            key: 'existing_key',
            userId: $user->id,
            action: 'checkout',
            payload: $payload,
            responseCode: 201,
            responseBody: ['order_id' => 456]
        );

        expect($result->id)->toBe($existing->id);
        expect($result->response_body)->toBe(['order_id' => 123]);
    });
});
