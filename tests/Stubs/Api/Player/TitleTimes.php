<?php

declare(strict_types=1);

namespace Bedriox\Api\Player;

final readonly class TitleTimes
{
    public function __construct(
        public int $fadeIn,
        public int $stay,
        public int $fadeOut,
    ) {}
}
