<?php

declare(strict_types=1);

namespace Bedriox\Api\Event\Processing;

use Bedriox\Api\Inventory\ItemStack;
use Bedriox\Api\Processing\FurnaceType;
use Bedriox\Api\World\BlockPosition;

final readonly class FurnaceSmeltedEvent
{
    public function __construct(
        public BlockPosition $position,
        public FurnaceType $furnaceType,
        public ItemStack $input,
        public ItemStack $result,
    ) {}
}
