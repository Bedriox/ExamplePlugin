<?php

declare(strict_types=1);

namespace Bedriox\ExamplePlugin;

use Bedriox\Api\Event\Block\BlockPlacedEvent;
use Bedriox\Api\Event\EventHandler;
use Bedriox\Api\Event\EventPriority;
use Bedriox\Api\Event\Player\PlayerChatEvent;
use Bedriox\Api\Event\Player\PlayerJoinEvent;
use Bedriox\Api\Plugin\Plugin;

final class Main extends Plugin
{
    public function onEnable(): void
    {
        $this->context()->events()->registerSubscriber($this);
        $this->logger()->info('ExamplePlugin enabled');
    }

    #[EventHandler]
    public function onJoin(PlayerJoinEvent $event): void
    {
        $this->context()->server()->sendMessage(
            $event->player,
            'Welcome to this Bedriox server, ' . $event->player->name . '!',
        );
    }

    #[EventHandler(priority: EventPriority::HIGH)]
    public function onChat(PlayerChatEvent $event): void
    {
        if (strcasecmp(trim($event->message()), 'cancel me') !== 0) {
            return;
        }
        $event->cancel();
        $this->context()->server()->sendMessage($event->player, 'ExamplePlugin cancelled that message.');
    }

    #[EventHandler(priority: EventPriority::MONITOR, receiveCancelled: true)]
    public function observeChat(PlayerChatEvent $event): void
    {
        $this->logger()->debug($event->isCancelled() ? 'Observed cancelled chat' : 'Observed accepted chat');
    }

    #[EventHandler]
    public function onBlockPlaced(BlockPlacedEvent $event): void
    {
        $this->logger()->debug(\sprintf(
            '%s placed %s at %d,%d,%d',
            $event->player->name,
            $event->block->identifier,
            $event->block->position->x,
            $event->block->position->y,
            $event->block->position->z,
        ));
    }
}
