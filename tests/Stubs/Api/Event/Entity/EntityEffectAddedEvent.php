<?php

declare(strict_types=1);

namespace Bedriox\Api\Event\Entity;

use Bedriox\Api\Effect\EffectCause;
use Bedriox\Api\Effect\EffectInstance;
use Bedriox\Api\Entity\LivingEntity;
use Bedriox\Api\Player\Player;

final readonly class EntityEffectAddedEvent
{
    public function __construct(
        public LivingEntity|Player $entity,
        public EffectInstance $effect,
        public EffectCause $cause,
        public ?EffectInstance $previous,
    ) {}
}
