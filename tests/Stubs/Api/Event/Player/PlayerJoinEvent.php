<?php

declare(strict_types=1);

namespace Bedriox\Api\Event\Player;

use Bedriox\Api\Player\Player;

final class PlayerJoinEvent
{
    public function __construct(public readonly Player $player, private ?string $joinMessage = null) {}

    public function getJoinMessage(): ?string
    {
        return $this->joinMessage;
    }

    public function setJoinMessage(?string $message): void
    {
        $this->joinMessage = $message;
    }
}
