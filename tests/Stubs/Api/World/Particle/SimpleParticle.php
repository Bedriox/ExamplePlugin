<?php

declare(strict_types=1);

namespace Bedriox\Api\World\Particle;

final readonly class SimpleParticle implements Particle
{
    public function __construct(private ParticleType $particleType) {}

    public function type(): ParticleType
    {
        return $this->particleType;
    }

    public function estimatedBytes(): int
    {
        return 32 + \strlen($this->particleType->value);
    }
}
