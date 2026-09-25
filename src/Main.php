<?php

declare(strict_types=1);

namespace Bedriox\ExamplePlugin;

use Bedriox\Api\Crafting\RecipeIngredient;
use Bedriox\Api\Crafting\ShapelessRecipe;
use Bedriox\Api\Event\Block\BlockPlacedEvent;
use Bedriox\Api\Event\EventHandler;
use Bedriox\Api\Event\EventPriority;
use Bedriox\Api\Event\Player\PlayerChatEvent;
use Bedriox\Api\Event\Player\PlayerCraftedItemEvent;
use Bedriox\Api\Event\Player\PlayerCraftItemEvent;
use Bedriox\Api\Event\Player\PlayerJoinEvent;
use Bedriox\Api\Inventory\ItemStack;
use Bedriox\Api\Plugin\Plugin;
use Bedriox\ExamplePlugin\Command\ExampleDisplayCommand;
use Bedriox\ExamplePlugin\Command\ExampleSenderCommand;

final class Main extends Plugin
{
    private const string EXAMPLE_RECIPE = 'exampleplugin:grass_block_from_dirt';

    public function onEnable(): void
    {
        $this->context()->events()->registerSubscriber($this);
        $this->context()->commands()->register(new ExampleSenderCommand());
        $this->context()->commands()->register(new ExampleDisplayCommand());
        $this->context()->recipes()->register(new ShapelessRecipe(
            self::EXAMPLE_RECIPE,
            [RecipeIngredient::exact('minecraft:dirt')],
            [new ItemStack('minecraft:grass_block', 1)],
        ));
        $this->logger()->info('ExamplePlugin enabled');
    }

    #[EventHandler(priority: EventPriority::HIGH)]
    public function onCraft(PlayerCraftItemEvent $event): void
    {
        if ($event->recipe->identifier() !== self::EXAMPLE_RECIPE || $event->craftCount <= 16) {
            return;
        }

        $event->cancel();
        $this->context()->server()->sendMessage(
            $event->player,
            'ExamplePlugin limits this recipe to 16 crafts per request.',
        );
    }

    #[EventHandler(priority: EventPriority::MONITOR)]
    public function onCrafted(PlayerCraftedItemEvent $event): void
    {
        if ($event->recipe->identifier() !== self::EXAMPLE_RECIPE) {
            return;
        }

        $this->logger()->debug(\sprintf('Observed %d completed example craft(s)', $event->craftCount));
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
