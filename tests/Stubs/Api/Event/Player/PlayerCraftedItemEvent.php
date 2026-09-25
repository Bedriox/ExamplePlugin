<?php

declare(strict_types=1);

namespace Bedriox\Api\Event\Player;

use Bedriox\Api\Crafting\CraftingGrid;
use Bedriox\Api\Crafting\CraftingRecipe;
use Bedriox\Api\Inventory\ItemStack;
use Bedriox\Api\Player\Player;

final readonly class PlayerCraftedItemEvent
{
    /**
     * @param list<ItemStack> $consumedInputs
     * @param list<ItemStack> $outputs
     * @param list<ItemStack> $remainders
     * @param list<ItemStack> $overflow
     */
    public function __construct(
        public Player $player,
        public CraftingRecipe $recipe,
        public CraftingGrid $grid,
        public int $craftCount,
        public array $consumedInputs,
        public array $outputs,
        public array $remainders = [],
        public array $overflow = [],
    ) {}
}
