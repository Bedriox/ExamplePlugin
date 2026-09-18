<?php

declare(strict_types=1);

namespace Bedriox\Api\Event\Player;

use Bedriox\Api\Player\Player;

final readonly class PlayerJoinEvent
{
    public function __construct(public Player $player) {}
}
