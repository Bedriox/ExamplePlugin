<?php

declare(strict_types=1);

namespace Bedriox\Api\Entity;

final readonly class CustomEntityState
{
    public function __construct(
        public int $schemaVersion,
        private string $payload,
    ) {}

    public function bytes(): string
    {
        return $this->payload;
    }

    public function size(): int
    {
        return \strlen($this->payload);
    }
}
