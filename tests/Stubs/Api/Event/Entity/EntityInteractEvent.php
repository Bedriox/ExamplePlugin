<?php

declare(strict_types=1);

namespace Bedriox\Api\Event\Entity;

use Bedriox\Api\Entity\Entity;
use Bedriox\Api\Entity\EntityInteractionType;
use Bedriox\Api\Player\Player;

final class EntityInteractEvent
{
    private bool $cancelled = false;

    public function __construct(
        public readonly Player $player,
        public readonly Entity $entity,
        public readonly EntityInteractionType $interaction,
    ) {}

    public function cancel(): void
    {
        $this->cancelled = true;
    }

    public function isCancelled(): bool
    {
        return $this->cancelled;
    }
}
