<?php

declare(strict_types=1);

namespace Bedriox\Api\Entity;

final readonly class CustomEntityType implements EntityType
{
    public function __construct(private string $identifier) {}

    public function identifier(): string
    {
        return $this->identifier;
    }
}
