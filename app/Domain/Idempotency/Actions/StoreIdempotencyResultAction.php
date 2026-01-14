<?php

declare(strict_types=1);

namespace App\Domain\Idempotency\Actions;

use App\Domain\Idempotency\Models\IdempotencyKey;
use Illuminate\Database\Eloquent\Model;

final class StoreIdempotencyResultAction
{
    public function execute(
        string $key,
        int $userId,
        string $action,
        array $payload,
        int $responseCode,
        array $responseBody
    ): IdempotencyKey|Model {
        $fingerprint = hash('sha256', json_encode($payload));

        return IdempotencyKey::query()->firstOrCreate(
            [
                'key' => $key,
                'action' => $action,
            ],
            [
                'user_id' => $userId,
                'request_fingerprint' => $fingerprint,
                'response_code' => $responseCode,
                'response_body' => $responseBody,
                'expires_at' => now()->addDay(),
                'created_at' => now(),
            ]
        );
    }
}
