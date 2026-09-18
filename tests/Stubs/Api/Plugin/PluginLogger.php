<?php

declare(strict_types=1);

namespace Bedriox\Api\Plugin;

interface PluginLogger
{
    public function debug(string $message): void;
    public function info(string $message): void;
}
