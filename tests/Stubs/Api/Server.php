<?php

declare(strict_types=1);

namespace Bedriox\Api;

interface Server
{
    public function broadcastMessage(string $message): int;
}
