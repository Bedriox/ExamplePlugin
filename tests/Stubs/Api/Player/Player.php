<?php

declare(strict_types=1);

namespace Bedriox\Api\Player;

use Bedriox\Api\Effect\EffectManager;
use Bedriox\Api\World\Position;

final class Player
{
    /** @var list<array{string, list<mixed>}> */
    public array $displays = [];

    private EffectManager $effects;

    public function __construct(
        public string $name,
        public string $uuid,
        public Position $position = new Position(0.0, 64.0, 0.0),
        public float $yaw = 0.0,
        public float $pitch = 0.0,
    ) {
        $this->effects = new EffectManager();
    }

    public function getEffects(): EffectManager
    {
        return $this->effects;
    }

    public function sendMessage(string $message): bool
    {
        return $this->record('message', [$message]);
    }
    public function sendPopup(string $message): bool
    {
        return $this->record('popup', [$message]);
    }
    public function sendJukeboxPopup(string $message): bool
    {
        return $this->record('jukebox', [$message]);
    }
    public function sendTip(string $message): bool
    {
        return $this->record('tip', [$message]);
    }
    public function sendTitle(string $title, string $subtitle = '', ?TitleTimes $times = null): bool
    {
        return $this->record('title', [$title, $subtitle, $times]);
    }
    public function sendSubTitle(string $subtitle): bool
    {
        return $this->record('subtitle', [$subtitle]);
    }
    public function sendActionBar(string $message): bool
    {
        return $this->record('actionbar', [$message]);
    }
    public function sendToast(string $title, string $body): bool
    {
        return $this->record('toast', [$title, $body]);
    }
    public function clearTitle(): bool
    {
        return $this->record('clear', []);
    }
    public function resetTitles(): bool
    {
        return $this->record('reset', []);
    }

    /** @param list<mixed> $arguments */
    private function record(string $method, array $arguments): bool
    {
        $this->displays[] = [$method, $arguments];

        return true;
    }
}
