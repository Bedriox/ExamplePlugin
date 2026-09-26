<?php

declare(strict_types=1);

namespace Bedriox\Api\Entity;

use Bedriox\Api\World\Position;

interface EntityRegistrar
{
    public function register(CustomMobDefinition $definition, bool $replace = false): void;

    public function spawn(CustomEntityType $type, Position $position, float $yaw = 0.0, float $pitch = 0.0): void;
}
