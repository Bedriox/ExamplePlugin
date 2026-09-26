<?php

declare(strict_types=1);

namespace Bedriox\Api\Entity;

use Closure;

final readonly class CustomMobDefinition
{
    /** @var Closure(): CustomMobBehavior */
    public Closure $factory;

    /** @param callable(): CustomMobBehavior $factory */
    public function __construct(
        public CustomEntityType $type,
        public VanillaEntityIdentity $networkAppearance,
        public EntityCategory $category,
        public float $width,
        public float $height,
        public float $maximumHealth,
        callable $factory,
        public CustomMobStateCodec $stateCodec,
        public int $maximumStateBytes,
        public bool $persistent = true,
    ) {
        $this->factory = Closure::fromCallable($factory);
    }
}
