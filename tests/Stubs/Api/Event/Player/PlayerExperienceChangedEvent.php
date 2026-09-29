<?php

declare(strict_types=1);

namespace Bedriox\Api\Event\Player;

use Bedriox\Api\Player\ExperienceChangeCause;
use Bedriox\Api\Player\ExperienceSnapshot;
use Bedriox\Api\Player\Player;

final readonly class PlayerExperienceChangedEvent
{
    public function __construct(
        public Player $player,
        public ExperienceSnapshot $previous,
        public ExperienceSnapshot $experience,
        public ExperienceChangeCause $cause,
    ) {}
}
