<?php

declare(strict_types=1);

namespace Bedriox\Api\Plugin\Data;

interface PluginData
{
    public function saveResource(string $name, bool $replace = false): bool;
    public function config(string $name = 'config.yml'): Configuration;
}
