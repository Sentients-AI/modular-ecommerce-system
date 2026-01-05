<?php

declare(strict_types=1);

namespace App\Domain\Idempotency\Actions;

use App\Domain\Idempotency\Models\IdempotencyKey;
use Carbon\Carbon;

final class RegisterKey
{
    /**
     * Execute the action to register an idempotency key.
     */
    public function execute(string $key, string $requestFingerprint, int $expiresInHours = 24): IdempotencyKey
    {
        return IdempotencyKey::query()->create([
            'key' => $key,
            'request_fingerprint' => $requestFingerprint,
            'expires_at' => Carbon::now()->addHours($expiresInHours),
        ]);
    }

    /**
     * Find an existing valid idempotency key.
     */
    public function find(string $key, string $requestFingerprint): ?IdempotencyKey
    {
        $idempotencyKey = IdempotencyKey::query()
            ->where('key', $key)
            ->where('request_fingerprint', $requestFingerprint)
            ->first();

        if ($idempotencyKey && $idempotencyKey->isValid()) {
            return $idempotencyKey;
        }

        return null;
    }

    /**
     * Store the response for an idempotency key.
     *
     * @param  array<string, mixed>  $responseBody
     */
    public function storeResponse(IdempotencyKey $idempotencyKey, int $responseCode, array $responseBody): IdempotencyKey
    {
        $idempotencyKey->update([
            'response_code' => $responseCode,
            'response_body' => $responseBody,
        ]);

        return $idempotencyKey->fresh();
    }
}
