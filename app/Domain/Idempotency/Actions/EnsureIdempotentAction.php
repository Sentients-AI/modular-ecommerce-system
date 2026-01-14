<?php

declare(strict_types=1);

namespace App\Domain\Idempotency\Actions;

use App\Domain\Idempotency\Models\IdempotencyKey;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class EnsureIdempotentAction
{
    public function execute(
        string $key,
        int $userId,
        string $action,
        array $payload
    ): ?array {
        $record = IdempotencyKey::query()->where('key', $key)
            ->where('user_id', $userId)
            ->where('action', $action)
            ->lockForUpdate()
            ->first();

        if (! $record) {
            return null;
        }

        if ($record->isExpired()) {
            $record->delete();

            return null;
        }

        $currentFingerprint = hash('sha256', json_encode($payload));
        if ($currentFingerprint !== $record->request_fingerprint) {
            throw new ConflictHttpException(
                'Idempotency key reused with different payload'
            );
        }

        return $record->response_body;
    }
}
