<?php

declare(strict_types=1);

namespace Bedriox\Api\Crafting;

use Bedriox\Api\Inventory\ItemStack;

final readonly class CraftingGrid
{
    /** @param list<ItemStack|null> $slots */
    public function __construct(
        public int $width,
        public int $height,
        public array $slots,
    ) {}
}
