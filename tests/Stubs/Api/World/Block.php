<?php

declare(strict_types=1);

namespace Bedriox\Api\World;

final readonly class Block
{
    public function __construct(public BlockPosition $position, public string $identifier) {}
}
