<?php

declare(strict_types=1);

namespace Bedriox\ExamplePlugin;

use Bedriox\Api\Command\AllowedCommandSenders;
use Bedriox\Api\Command\CommandContext;
use Bedriox\Api\Command\CommandDefinition;
use Bedriox\Api\Command\CommandResult;
use Bedriox\Api\Command\ConsoleCommandSender;
use Bedriox\Api\Command\PlayerCommandSender;
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
        $this->context()->commands()->register(
            new CommandDefinition(
                'examplesender',
                'Shows whether a command came from the console or a player.',
                'examplesender',
                allowedSenders: AllowedCommandSenders::ANY,
            ),
            $this->showCommandSender(...),
        );
        $this->logger()->info('ExamplePlugin enabled');
    }

    private function showCommandSender(CommandContext $context): CommandResult
    {
        if ($context->arguments() !== []) {
            return CommandResult::USAGE;
        }

        $sender = $context->sender();
        if ($sender instanceof PlayerCommandSender) {
            $sender->sendMessage('This command was sent by player ' . $sender->player()->name . '.');
            return CommandResult::SUCCESS;
        }
        if ($sender instanceof ConsoleCommandSender) {
            $sender->sendMessage('This command was sent from the server console.');
            return CommandResult::SUCCESS;
        }

        $sender->sendMessage('This command sender is not supported.');
        return CommandResult::FAILURE;
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
