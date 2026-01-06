<?php

namespace App\Domain\Idempotency\Actions;

use App\Domain\Idempotency\Models\IdempotencyKey;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class EnsureIdempotentAction
{
    public function execute(
        string $key,
        int $userId,
        string $action,
        array $payload
    ): ?array {
        $hash = Hash::make(json_encode($payload));

        $record = IdempotencyKey::query()->where('key', $key)
            ->where('user_id', $userId)
            ->where('action', $action)
            ->first();

        if (! $record) {
            return null;
        }

        if (! Hash::check(json_encode($payload), $record->request_hash)) {
            throw new ConflictHttpException(
                'Idempotency key reused with different payload'
            );
        }

        return $record->response;
    }
}
