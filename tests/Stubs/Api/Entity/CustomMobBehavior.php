<?php

declare(strict_types=1);

namespace Bedriox\Api\Entity;

abstract class CustomMobBehavior
{
    public function onSpawn(CustomMobSpawnContext $context): void {}

    public function onTick(CustomMobTickContext $context): void {}

    public function onAiTick(CustomMobTickContext $context): void {}

    public function onDespawn(CustomMobDespawnContext $context): void {}
}
