<?php

declare(strict_types=1);

namespace Bedriox\Api\Player;

final readonly class Player
{
    public function __construct(public string $name, public string $uuid) {}
}
