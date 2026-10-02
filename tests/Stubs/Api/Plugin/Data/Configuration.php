<?php

declare(strict_types=1);

namespace Bedriox\Api\Plugin\Data;

interface Configuration
{
    public function getString(string $key, string $default = ''): string;
    public function getBool(string $key, bool $default = false): bool;
}
