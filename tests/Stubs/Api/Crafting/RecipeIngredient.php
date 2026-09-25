<?php

declare(strict_types=1);

namespace Bedriox\Api\Crafting;

use Bedriox\Api\Inventory\ItemNbt;

final readonly class RecipeIngredient
{
    /** @param list<string> $identifiers */
    public function __construct(
        public array $identifiers,
        public int $count = 1,
        public ?int $auxValue = null,
        public ?int $damage = null,
        public ?ItemNbt $nbt = null,
    ) {}

    public static function exact(
        string $identifier,
        int $count = 1,
        ?int $auxValue = null,
        ?int $damage = null,
        ?ItemNbt $nbt = null,
    ): self {
        return new self([$identifier], $count, $auxValue, $damage, $nbt);
    }
}
