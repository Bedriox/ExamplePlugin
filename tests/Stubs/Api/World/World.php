<?php

declare(strict_types=1);

namespace Bedriox\Api\World;

use Bedriox\Api\Player\Player;
use Bedriox\Api\World\Particle\Particle;

final class World
{
    /** @var list<array{Position, Particle, list<Player>|null}> */
    public array $particles = [];

    public function __construct(
        private string $id,
        private int $loadGeneration,
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function loadGeneration(): int
    {
        return $this->loadGeneration;
    }

    /** @param list<Player>|null $players */
    public function spawnParticle(Position $position, Particle $particle, ?array $players = null): void
    {
        $this->particles[] = [$position, $particle, $players];
    }
}
