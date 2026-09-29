<?php

declare(strict_types=1);

namespace Bedriox\Api\Player;

final readonly class ExperienceSnapshot
{
    public int $level;
    public float $progress;

    public function __construct(public int $totalPoints)
    {
        $this->level = 0;
        $this->progress = 0.0;
    }
}
