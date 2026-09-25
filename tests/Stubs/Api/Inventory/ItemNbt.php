<?php

declare(strict_types=1);

namespace Bedriox\Api\Inventory;

final readonly class ItemNbt
{
    private function __construct() {}

    public static function empty(): self
    {
        return new self();
    }
}
