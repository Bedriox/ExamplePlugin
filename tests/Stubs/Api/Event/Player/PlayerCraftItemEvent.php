<?php

declare(strict_types=1);

namespace Bedriox\Api\Event\Player;

use Bedriox\Api\Crafting\CraftingGrid;
use Bedriox\Api\Crafting\CraftingRecipe;
use Bedriox\Api\Inventory\ItemStack;
use Bedriox\Api\Player\Player;

final class PlayerCraftItemEvent
{
    private bool $cancelled = false;

    /** @var list<ItemStack> */
    public readonly array $consumedInputs;

    /** @var list<ItemStack> */
    public readonly array $originalOutputs;

    /** @var list<ItemStack> */
    public readonly array $remainders;

    /** @var list<ItemStack> */
    private array $currentOutputs;

    /**
     * @param list<ItemStack> $consumedInputs
     * @param list<ItemStack> $outputs
     * @param list<ItemStack> $remainders
     */
    public function __construct(
        public readonly Player $player,
        public readonly CraftingRecipe $recipe,
        public readonly CraftingGrid $grid,
        public readonly int $craftCount,
        array $consumedInputs,
        array $outputs,
        array $remainders = [],
    ) {
        $this->consumedInputs = $consumedInputs;
        $this->originalOutputs = $outputs;
        $this->currentOutputs = $outputs;
        $this->remainders = $remainders;
    }

    /** @return list<ItemStack> */
    public function outputs(): array
    {
        return $this->currentOutputs;
    }

    /** @param list<ItemStack> $outputs */
    public function setOutputs(array $outputs): void
    {
        $this->currentOutputs = $outputs;
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
