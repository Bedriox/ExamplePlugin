<?php

declare(strict_types=1);

namespace Bedriox\Api\Inventory;

final readonly class ItemStack
{
    public function __construct(
        public string $identifier,
        public int $count,
        public int $damage = 0,
        public ?ItemNbt $nbt = null,
        public int $auxValue = 0,
    ) {}
}
