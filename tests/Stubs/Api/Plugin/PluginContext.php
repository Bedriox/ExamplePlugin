<?php

declare(strict_types=1);

namespace Bedriox\Api\Plugin;

use Bedriox\Api\Event\EventRegistrar;
use Bedriox\Api\Server;

final readonly class PluginContext
{
    public function __construct(
        private PluginLogger $logger,
        private EventRegistrar $events,
        private Server $server,
    ) {}

    public function logger(): PluginLogger
    {
        return $this->logger;
    }
    public function events(): EventRegistrar
    {
        return $this->events;
    }
    public function server(): Server
    {
        return $this->server;
    }
}
