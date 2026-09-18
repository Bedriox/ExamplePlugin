<?php

declare(strict_types=1);

namespace Bedriox\Api;

use Bedriox\Api\Player\Player;

interface Server
{
    public function sendMessage(Player $player, string $message): void;
}
