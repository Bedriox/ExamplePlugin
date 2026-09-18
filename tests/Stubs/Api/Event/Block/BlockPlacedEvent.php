<?php

declare(strict_types=1);

namespace Bedriox\Api\Event\Block;

use Bedriox\Api\Player\Player;
use Bedriox\Api\World\Block;

final readonly class BlockPlacedEvent
{
    public function __construct(public Player $player, public Block $block) {}
}
