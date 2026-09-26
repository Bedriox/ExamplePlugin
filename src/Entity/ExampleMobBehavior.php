<?php

declare(strict_types=1);

namespace Bedriox\ExamplePlugin\Entity;

use Bedriox\Api\Entity\CustomMobBehavior;
use Bedriox\Api\Entity\CustomMobDespawnContext;
use Bedriox\Api\Entity\CustomMobSpawnContext;
use Bedriox\Api\Entity\CustomMobTickContext;
use Bedriox\Api\World\Position;

final class ExampleMobBehavior extends CustomMobBehavior
{
    private int $lifetimeTicks = 0;

    private bool $spawned = false;

    public function onSpawn(CustomMobSpawnContext $context): void
    {
        $this->spawned = true;
    }

    public function onTick(CustomMobTickContext $context): void
    {
        if ($this->spawned && $this->lifetimeTicks < ExampleMobStateCodec::MAXIMUM_LIFETIME_TICKS) {
            ++$this->lifetimeTicks;
        }
    }

    public function onAiTick(CustomMobTickContext $context): void
    {
        if (!$this->spawned || $context->currentTick % 20 !== 0) {
            return;
        }

        $position = $context->mob->getPosition();
        [$xOffset, $zOffset] = match (intdiv($context->currentTick, 20) % 4) {
            0 => [2.0, 0.0],
            1 => [0.0, 2.0],
            2 => [-2.0, 0.0],
            default => [0.0, -2.0],
        };
        $target = new Position($position->x + $xOffset, $position->y, $position->z + $zOffset);
        $context->controller->moveToward($target, 0.12);
        $context->controller->lookAt($target);
    }

    public function onDespawn(CustomMobDespawnContext $context): void
    {
        $this->spawned = false;
    }

    public function lifetimeTicks(): int
    {
        return $this->lifetimeTicks;
    }

    public function restoreLifetimeTicks(int $lifetimeTicks): void
    {
        if ($lifetimeTicks < 0 || $lifetimeTicks > ExampleMobStateCodec::MAXIMUM_LIFETIME_TICKS) {
            throw new \InvalidArgumentException('Example mob lifetime is outside its supported range.');
        }

        $this->lifetimeTicks = $lifetimeTicks;
    }
}
