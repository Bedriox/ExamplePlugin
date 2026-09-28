<?php

declare(strict_types=1);

namespace Bedriox\Api\World;

final readonly class World
{
    public function __construct(
        private string $id,
        private int $loadGeneration,
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function loadGeneration(): int
    {
        return $this->loadGeneration;
    }
}
