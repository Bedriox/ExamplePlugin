<?php

declare(strict_types=1);

namespace Bedriox\Api\Event\Player;

use Bedriox\Api\Player\Player;

final class PlayerChatEvent
{
    private bool $cancelled = false;

    public function __construct(public readonly Player $player, private string $message) {}
    public function message(): string
    {
        return $this->message;
    }
    public function cancel(): void
    {
        $this->cancelled = true;
    }
    public function isCancelled(): bool
    {
        return $this->cancelled;
    }
}
