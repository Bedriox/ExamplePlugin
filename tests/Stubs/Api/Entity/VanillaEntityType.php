<?php

declare(strict_types=1);

namespace Bedriox\Api\Entity;

enum VanillaEntityType: string implements VanillaEntityIdentity
{
    case COW = 'minecraft:cow';
    case ZOMBIE = 'minecraft:zombie';

    public function identifier(): string
    {
        return $this->value;
    }
}
