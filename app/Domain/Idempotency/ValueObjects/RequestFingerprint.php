<?php

declare(strict_types=1);

namespace App\Domain\Idempotency\ValueObjects;

use Illuminate\Support\Arr;
use JsonException;

final readonly class RequestFingerprint
{
    private function __construct(
        public string $hash
    ) {}

    /**
     * @throws JsonException
     */
    public static function fromArray(array $payload): self
    {
        $normalized = self::normalize($payload);

        return new self(
            hash('sha256', json_encode($normalized, JSON_THROW_ON_ERROR))
        );
    }

    public function equals(self $other): bool
    {
        return hash_equals($this->hash, $other->hash);
    }

    private static function normalize(array $payload): array
    {
        // 1️⃣ Remove non-deterministic fields
        Arr::forget($payload, [
            'timestamp',
            'nonce',
            'signature',
        ]);

        // 2️⃣ Sort keys recursively
        return self::recursiveKeySort($payload);
    }

    private static function recursiveKeySort(array $data): array
    {
        foreach ($data as &$value) {
            if (is_array($value)) {
                $value = self::recursiveKeySort($value);
            }
        }

        ksort($data);

        return $data;
    }
}
