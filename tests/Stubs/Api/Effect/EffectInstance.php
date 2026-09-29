<?php

declare(strict_types=1);

namespace Bedriox\Api\Effect;

final readonly class EffectInstance
{
    public function __construct(
        public EffectType $type,
        public int $durationTicks,
        public int $amplifier = 0,
        public bool $visible = true,
        public bool $ambient = false,
        public bool $infinite = false,
    ) {}
}
