<?php

declare(strict_types=1);

namespace Bedriox\Api\Event\Entity;

use Bedriox\Api\Entity\Entity;
use Bedriox\Api\Entity\SpawnCause;

final readonly class EntitySpawnedEvent
{
    public function __construct(
        public Entity $entity,
        public SpawnCause $cause,
    ) {}
}
