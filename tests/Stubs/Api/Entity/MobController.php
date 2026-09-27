<?php

declare(strict_types=1);

namespace Bedriox\Api\Entity;

use Bedriox\Api\World\Position;

interface CustomMobController
{
    public function moveToward(Position $target, float $speed): void;

    public function lookAt(Position $target): void;

    public function target(Entity $target, float $speed): void;

    public function setVelocity(float $x, float $y, float $z): void;

    public function despawn(): void;
}
