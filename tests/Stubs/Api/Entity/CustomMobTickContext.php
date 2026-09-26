<?php

declare(strict_types=1);

namespace Bedriox\Api\Entity;

final readonly class CustomMobTickContext
{
    public function __construct(
        public Mob $mob,
        public int $currentTick,
        public CustomMobController $controller,
    ) {}
}
