<?php

declare(strict_types=1);

namespace Bedriox\ExamplePlugin;

use Bedriox\Api\Crafting\RecipeIngredient;
use Bedriox\Api\Crafting\ShapelessRecipe;
use Bedriox\Api\Entity\CustomEntityType;
use Bedriox\Api\Entity\CustomMobDefinition;
use Bedriox\Api\Entity\EntityCategory;
use Bedriox\Api\Entity\VanillaEntityIdentifier;
use Bedriox\Api\Event\Block\BlockPlacedEvent;
use Bedriox\Api\Event\Entity\EntityEffectAddedEvent;
use Bedriox\Api\Event\Entity\EntityInteractEvent;
use Bedriox\Api\Event\Entity\EntitySpawnedEvent;
use Bedriox\Api\Event\EventHandler;
use Bedriox\Api\Event\EventPriority;
use Bedriox\Api\Event\Player\PlayerChatEvent;
use Bedriox\Api\Event\Player\PlayerCraftedItemEvent;
use Bedriox\Api\Event\Player\PlayerCraftItemEvent;
use Bedriox\Api\Event\Player\PlayerJoinEvent;
use Bedriox\Api\Inventory\ItemStack;
use Bedriox\Api\Player\Player as ApiPlayer;
use Bedriox\Api\Plugin\Plugin;
use Bedriox\ExamplePlugin\Command\ExampleDisplayCommand;
use Bedriox\ExamplePlugin\Command\ExampleEffectCommand;
use Bedriox\ExamplePlugin\Command\ExampleSenderCommand;
use Bedriox\ExamplePlugin\Command\ExampleSpawnCommand;
use Bedriox\ExamplePlugin\Entity\ExampleMobBehavior;
use Bedriox\ExamplePlugin\Entity\ExampleMobStateCodec;

final class Main extends Plugin
{
    private const string EXAMPLE_RECIPE = 'exampleplugin:grass_block_from_dirt';
    private const string EXAMPLE_MOB = 'exampleplugin:guide';

    public function onEnable(): void
    {
        $this->context()->events()->registerSubscriber($this);
        $this->context()->commands()->register(new ExampleSenderCommand());
        $this->context()->commands()->register(new ExampleDisplayCommand());
        $this->context()->commands()->register(new ExampleEffectCommand());
        $exampleMob = new CustomEntityType(self::EXAMPLE_MOB);
        $entities = $this->context()->entities();
        $entities->register(new CustomMobDefinition(
            $exampleMob,
            new VanillaEntityIdentifier('minecraft:cow'),
            EntityCategory::ANIMAL,
            width: 0.9,
            height: 1.4,
            maximumHealth: 10.0,
            factory: static fn(): ExampleMobBehavior => new ExampleMobBehavior(),
            stateCodec: new ExampleMobStateCodec(),
            maximumStateBytes: ExampleMobStateCodec::MAXIMUM_STATE_BYTES,
        ));
        $this->context()->commands()->register(new ExampleSpawnCommand($entities, $exampleMob));
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
        $event->player->sendMessage('ExamplePlugin limits this recipe to 16 crafts per request.');
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
        $event->player->sendMessage('Welcome to this Bedriox server, ' . $event->player->name . '!');
    }

    #[EventHandler(priority: EventPriority::HIGH)]
    public function onChat(PlayerChatEvent $event): void
    {
        if (strcasecmp(trim($event->message()), 'cancel me') !== 0) {
            return;
        }
        $event->cancel();
        $event->player->sendMessage('ExamplePlugin cancelled that message.');
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

    #[EventHandler]
    public function onExampleMobInteract(EntityInteractEvent $event): void
    {
        if ($event->entity->getType()->identifier() !== self::EXAMPLE_MOB) {
            return;
        }

        $event->cancel();
        $event->player->sendMessage('You found the ExamplePlugin guide mob.');
    }

    #[EventHandler(priority: EventPriority::MONITOR)]
    public function onExampleMobSpawned(EntitySpawnedEvent $event): void
    {
        if ($event->entity->getType()->identifier() !== self::EXAMPLE_MOB) {
            return;
        }

        $this->logger()->debug('Observed an ExamplePlugin guide mob spawn from ' . $event->cause->value . '.');
    }

    #[EventHandler(priority: EventPriority::MONITOR)]
    public function onEffectAdded(EntityEffectAddedEvent $event): void
    {
        $this->logger()->debug(\sprintf(
            'Observed %s applied to %s by %s.',
            $event->effect->type->value,
            $event->entity instanceof ApiPlayer ? $event->entity->uuid : $event->entity->getUniqueId(),
            $event->cause->value,
        ));
    }
}
