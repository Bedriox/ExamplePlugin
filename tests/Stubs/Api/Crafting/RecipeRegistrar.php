<?php

declare(strict_types=1);

namespace Bedriox\Api\Crafting;

interface RecipeRegistrar
{
    public function register(ShapedRecipe|ShapelessRecipe $recipe, bool $replace = false): void;
}
