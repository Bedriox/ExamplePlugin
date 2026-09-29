<?php

declare(strict_types=1);

namespace Bedriox\Api\World\Particle;

interface Particle
{
    public function estimatedBytes(): int;
}
