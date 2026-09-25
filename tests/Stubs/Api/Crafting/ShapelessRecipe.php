<?php

declare(strict_types=1);

namespace Bedriox\Api\Crafting;

use Bedriox\Api\Inventory\ItemStack;

final readonly class ShapelessRecipe implements CraftingRecipe
{
    /**
     * @param list<RecipeIngredient> $ingredients
     * @param list<ItemStack> $outputs
     */
    public function __construct(
        private string $id,
        public array $ingredients,
        public array $outputs,
        public int $recipePriority = 0,
    ) {}

    public function identifier(): string
    {
        return $this->id;
    }

    public function priority(): int
    {
        return $this->recipePriority;
    }

    /** @return list<ItemStack> */
    public function outputs(): array
    {
        return $this->outputs;
    }
}
