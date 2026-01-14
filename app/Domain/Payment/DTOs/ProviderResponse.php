<?php

declare(strict_types=1);

namespace App\Domain\Payment\DTOs;

final readonly class ProviderResponse
{
    public function __construct(
        private string $provider,
        private string $reference,
        private ?string $clientSecret = null,
    ) {}

    public function provider(): string
    {
        return $this->provider;
    }

    public function reference(): string
    {
        return $this->reference;
    }

    public function clientSecret(): ?string
    {
        return $this->clientSecret;
    }
}
