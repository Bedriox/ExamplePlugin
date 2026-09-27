<?php

declare(strict_types=1);

namespace Bedriox\ExamplePlugin\Tests;

use Bedriox\Api\Command\AllowedCommandSenders;
use Bedriox\Api\Command\Command;
use Bedriox\Api\Command\CommandContext;
use Bedriox\Api\Command\CommandJob;
use Bedriox\Api\Command\CommandJobSubscription;
use Bedriox\Api\Command\CommandRegistrar;
use Bedriox\Api\Command\CommandResult;
use Bedriox\Api\Command\CommandSenderType;
use Bedriox\Api\Command\CommandSoftEnum;
use Bedriox\Api\Command\CommandSubscription;
use Bedriox\Api\Command\CommandValues;
use Bedriox\Api\Command\ConsoleCommandSender;
use Bedriox\Api\Command\PlayerCommandSender;
use Bedriox\Api\Crafting\CraftingGrid;
use Bedriox\Api\Crafting\RecipeRegistrar;
use Bedriox\Api\Crafting\ShapedRecipe;
use Bedriox\Api\Crafting\ShapelessRecipe;
use Bedriox\Api\Entity\CustomEntityState;
use Bedriox\Api\Entity\CustomEntityType;
use Bedriox\Api\Entity\CustomMobDefinition;
use Bedriox\Api\Entity\CustomMobDespawnContext;
use Bedriox\Api\Entity\CustomMobSpawnContext;
use Bedriox\Api\Entity\CustomMobTickContext;
use Bedriox\Api\Entity\Entity;
use Bedriox\Api\Entity\EntityCategory;
use Bedriox\Api\Entity\EntityInteractionType;
use Bedriox\Api\Entity\EntityRegistrar;
use Bedriox\Api\Entity\EntityType;
use Bedriox\Api\Entity\Mob;
use Bedriox\Api\Entity\MobActivationState;
use Bedriox\Api\Entity\MobController;
use Bedriox\Api\Entity\SpawnCause;
use Bedriox\Api\Event\Entity\EntityInteractEvent;
use Bedriox\Api\Event\Entity\EntitySpawnedEvent;
use Bedriox\Api\Event\EventHandler;
use Bedriox\Api\Event\EventPriority;
use Bedriox\Api\Event\EventRegistrar;
use Bedriox\Api\Event\Player\PlayerChatEvent;
use Bedriox\Api\Event\Player\PlayerCraftedItemEvent;
use Bedriox\Api\Event\Player\PlayerCraftItemEvent;
use Bedriox\Api\Event\Player\PlayerJoinEvent;
use Bedriox\Api\Inventory\ItemStack;
use Bedriox\Api\Player\Player;
use Bedriox\Api\Plugin\PluginContext;
use Bedriox\Api\Plugin\PluginLogger;
use Bedriox\Api\Plugin\SourcePluginDefinition;
use Bedriox\Api\Plugin\SourcePluginRegistrar;
use Bedriox\Api\Server;
use Bedriox\Api\World\Position;
use Bedriox\ExamplePlugin\Command\DisplayMode;
use Bedriox\ExamplePlugin\Entity\ExampleMobBehavior;
use Bedriox\ExamplePlugin\Entity\ExampleMobStateCodec;
use Bedriox\ExamplePlugin\Main;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use RuntimeException;

final class ExamplePluginTest extends TestCase
{
    public function testLifecycleRegistersSubscriberAndGreetsJoiningPlayer(): void
    {
        $registrar = new RecordingRegistrar();
        $commands = new RecordingCommandRegistrar();
        $logger = new RecordingLogger();
        $server = new RecordingServer();
        $recipes = new RecordingRecipeRegistrar();
        $entities = new RecordingEntityRegistrar();
        $plugin = new Main(self::context($logger, $registrar, $commands, $server, $recipes, $entities));
        $player = new Player('Alex', 'uuid-one');

        $plugin->onEnable();
        $plugin->onJoin(new PlayerJoinEvent($player));

        self::assertSame([$plugin], $registrar->subscribers);
        self::assertCount(3, $commands->commands);
        self::assertSame('examplesender', $commands->commands[0]->definition()->name);
        self::assertSame('examplespawn', $commands->commands[2]->definition()->name);
        self::assertCount(1, $recipes->recipes);
        self::assertSame('exampleplugin:grass_block_from_dirt', $recipes->recipes[0]->identifier());
        self::assertCount(1, $entities->definitions);
        self::assertSame('exampleplugin:guide', $entities->definitions[0]->type->identifier());
        self::assertSame(['ExamplePlugin enabled'], $logger->info);
        self::assertSame([['uuid-one', 'Welcome to this Bedriox server, Alex!']], $server->messages);
    }

    public function testChatExampleCancelsOnlyTheDocumentedPhrase(): void
    {
        $server = new RecordingServer();
        $plugin = new Main(self::context(
            new RecordingLogger(),
            new RecordingRegistrar(),
            new RecordingCommandRegistrar(),
            $server,
        ));
        $player = new Player('Alex', 'uuid-one');
        $accepted = new PlayerChatEvent($player, 'hello');
        $cancelled = new PlayerChatEvent($player, '  Cancel Me  ');

        $plugin->onChat($accepted);
        $plugin->onChat($cancelled);

        self::assertFalse($accepted->isCancelled());
        self::assertTrue($cancelled->isCancelled());
        self::assertSame([['uuid-one', 'ExamplePlugin cancelled that message.']], $server->messages);
    }

    public function testCraftingExampleRegistersObservesAndBoundsItsRecipe(): void
    {
        $logger = new RecordingLogger();
        $server = new RecordingServer();
        $recipes = new RecordingRecipeRegistrar();
        $plugin = new Main(self::context(
            $logger,
            new RecordingRegistrar(),
            new RecordingCommandRegistrar(),
            $server,
            $recipes,
        ));
        $plugin->onEnable();

        $recipe = $recipes->recipes[0];
        $player = new Player('Alex', 'uuid-one');
        $input = new ItemStack('minecraft:dirt', 17);
        $output = new ItemStack('minecraft:grass_block', 1);
        $grid = new CraftingGrid(2, 2, [$input, null, null, null]);
        $allowed = new PlayerCraftItemEvent($player, $recipe, $grid, 16, [$input], [$output]);
        $limited = new PlayerCraftItemEvent($player, $recipe, $grid, 17, [$input], [$output]);

        $plugin->onCraft($allowed);
        $plugin->onCraft($limited);
        $plugin->onCrafted(new PlayerCraftedItemEvent($player, $recipe, $grid, 1, [$input], [$output]));

        self::assertFalse($allowed->isCancelled());
        self::assertTrue($limited->isCancelled());
        self::assertSame(
            [['uuid-one', 'ExamplePlugin limits this recipe to 16 crafts per request.']],
            $server->messages,
        );
        self::assertContains('Observed 1 completed example craft(s)', $logger->debug);
    }

    public function testExampleSenderCommandDistinguishesConsoleAndPlayer(): void
    {
        $commands = new RecordingCommandRegistrar();
        $plugin = new Main(self::context(
            new RecordingLogger(),
            new RecordingRegistrar(),
            $commands,
            new RecordingServer(),
        ));
        $plugin->onEnable();

        $definition = $commands->commands[0]->definition();
        self::assertSame(AllowedCommandSenders::ANY, $definition->allowedSenders);
        self::assertNull($definition->permission);

        $console = new RecordingConsoleSender();
        self::assertTrue($commands->dispatch(0, new CommandContext(
            $console,
            'examplesender',
            new CommandValues(),
        ))->isSuccess());
        self::assertSame(['This command was sent from the server console.'], $console->messages);

        $player = new RecordingPlayerSender(new Player('Alex', 'uuid-one'));
        self::assertTrue($commands->dispatch(0, new CommandContext(
            $player,
            'examplesender',
            new CommandValues(),
        ))->isSuccess());
        self::assertSame(['This command was sent by player Alex.'], $player->messages);
    }

    public function testExampleSenderCommandDeclaresNoArguments(): void
    {
        $commands = new RecordingCommandRegistrar();
        $plugin = new Main(self::context(
            new RecordingLogger(),
            new RecordingRegistrar(),
            $commands,
            new RecordingServer(),
        ));
        $plugin->onEnable();
        self::assertSame([], $commands->commands[0]->defineArguments()->parameters());
    }

    public function testPlayerDisplayCommandDemonstratesEveryHighLevelPresentation(): void
    {
        $commands = new RecordingCommandRegistrar();
        $plugin = new Main(self::context(
            new RecordingLogger(),
            new RecordingRegistrar(),
            $commands,
            new RecordingServer(),
        ));
        $plugin->onEnable();
        $player = new Player('Alex', 'uuid-one');
        $sender = new RecordingPlayerSender($player);

        self::assertSame('exampledisplay', $commands->commands[1]->definition()->name);
        self::assertSame(AllowedCommandSenders::PLAYER_ONLY, $commands->commands[1]->definition()->allowedSenders);
        $parameter = $commands->commands[1]->defineArguments()->parameters()[0];
        self::assertSame('display', $parameter->name);
        self::assertSame(array_column(DisplayMode::cases(), 'value'), $parameter->choices);
        foreach (DisplayMode::cases() as $display) {
            self::assertTrue($commands->dispatch(1, new CommandContext(
                $sender,
                'exampledisplay',
                new CommandValues(['display' => $display]),
            ))->isSuccess());
        }
        self::assertSame(
            ['message', 'popup', 'jukebox', 'tip', 'title', 'subtitle', 'actionbar', 'toast', 'clear', 'reset'],
            array_column($player->displays, 0),
        );
    }

    public function testCustomMobCanBeSpawnedAndItsBoundedStateCanBeRestored(): void
    {
        $commands = new RecordingCommandRegistrar();
        $entities = new RecordingEntityRegistrar();
        $plugin = new Main(self::context(
            new RecordingLogger(),
            new RecordingRegistrar(),
            $commands,
            new RecordingServer(),
            entities: $entities,
        ));
        $plugin->onEnable();

        $player = new Player('Alex', 'uuid-one', new Position(10.0, 64.0, 20.0), 90.0);
        $result = $commands->dispatch(2, new CommandContext(
            new RecordingPlayerSender($player),
            'examplespawn',
            new CommandValues(),
        ));

        self::assertTrue($result->isSuccess());
        self::assertSame('Spawned the ExamplePlugin guide mob.', $result->message());
        self::assertCount(1, $entities->spawns);
        self::assertSame('exampleplugin:guide', $entities->spawns[0][0]->identifier());
        self::assertEqualsWithDelta(8.0, $entities->spawns[0][1]->x, 0.0001);
        self::assertEqualsWithDelta(20.0, $entities->spawns[0][1]->z, 0.0001);
        self::assertSame(90.0, $entities->spawns[0][2]);

        $definition = $entities->definitions[0];
        self::assertSame('minecraft:cow', $definition->networkAppearance->identifier());
        $behavior = ($definition->factory)();
        self::assertInstanceOf(ExampleMobBehavior::class, $behavior);
        $mob = new RecordingMob($definition->type);
        $controller = new RecordingMobController();
        $behavior->onSpawn(new CustomMobSpawnContext($mob, SpawnCause::PLUGIN));
        $behavior->onTick(new CustomMobTickContext($mob, 1, $controller));
        $behavior->onAiTick(new CustomMobTickContext($mob, 20, $controller));
        self::assertCount(1, $controller->moves);
        self::assertCount(1, $controller->looks);
        self::assertSame(0.12, $controller->moves[0][1]);
        self::assertSame(2.0, $controller->moves[0][0]->z);
        $state = $definition->stateCodec->encode($behavior);
        self::assertSame('1', $state->bytes());
        $restored = new ExampleMobBehavior();
        $definition->stateCodec->restore($restored, $state);
        self::assertSame(1, $restored->lifetimeTicks());
        $behavior->onDespawn(new CustomMobDespawnContext($mob));
    }

    public function testTypedEntityEventsObserveSpawnsAndCancelGuideInteraction(): void
    {
        $logger = new RecordingLogger();
        $server = new RecordingServer();
        $plugin = new Main(self::context(
            $logger,
            new RecordingRegistrar(),
            new RecordingCommandRegistrar(),
            $server,
        ));
        $entity = new RecordingMob(new CustomEntityType('exampleplugin:guide'));
        $player = new Player('Alex', 'uuid-one');

        $plugin->onExampleMobSpawned(new EntitySpawnedEvent($entity, SpawnCause::PLUGIN));
        $interaction = new EntityInteractEvent($player, $entity, EntityInteractionType::INTERACT);
        $plugin->onExampleMobInteract($interaction);

        self::assertTrue($interaction->isCancelled());
        self::assertSame(
            [['uuid-one', 'You found the ExamplePlugin guide mob.']],
            $server->messages,
        );
        self::assertContains('Observed an ExamplePlugin guide mob spawn from plugin.', $logger->debug);
    }

    public function testCustomMobStateRejectsMalformedPayload(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        new ExampleMobStateCodec()->restore(
            new ExampleMobBehavior(),
            new CustomEntityState(1, 'not-a-tick-count'),
        );
    }

    public function testAttributesDemonstrateDefaultHighAndMonitorPriorities(): void
    {
        self::assertSame(EventPriority::NORMAL, self::handler('onJoin')->priority);
        self::assertSame(EventPriority::HIGH, self::handler('onChat')->priority);
        self::assertSame(EventPriority::HIGH, self::handler('onCraft')->priority);
        self::assertSame(EventPriority::MONITOR, self::handler('onCrafted')->priority);
        self::assertSame(EventPriority::NORMAL, self::handler('onExampleMobInteract')->priority);
        self::assertSame(EventPriority::MONITOR, self::handler('onExampleMobSpawned')->priority);
        $monitor = self::handler('observeChat');
        self::assertSame(EventPriority::MONITOR, $monitor->priority);
        self::assertTrue($monitor->receiveCancelled);
    }

    private static function handler(string $method): EventHandler
    {
        $attributes = new ReflectionMethod(Main::class, $method)->getAttributes(EventHandler::class);
        self::assertCount(1, $attributes);

        return $attributes[0]->newInstance();
    }

    private static function context(
        RecordingLogger $logger,
        RecordingRegistrar $events,
        RecordingCommandRegistrar $commands,
        RecordingServer $server,
        ?RecordingRecipeRegistrar $recipes = null,
        ?RecordingEntityRegistrar $entities = null,
    ): PluginContext {
        return new PluginContext(
            'ExamplePlugin',
            $logger,
            $events,
            $commands,
            new UnusedSourcePluginRegistrar(),
            $server,
            __DIR__ . '/plugin_data/ExamplePlugin',
            recipes: $recipes ?? new RecordingRecipeRegistrar(),
            entities: $entities ?? new RecordingEntityRegistrar(),
        );
    }
}

final class RecordingEntityRegistrar implements EntityRegistrar
{
    /** @var list<CustomMobDefinition> */
    public array $definitions = [];

    /** @var list<array{CustomEntityType, Position, float, float}> */
    public array $spawns = [];

    public function register(CustomMobDefinition $definition, bool $replace = false): void
    {
        $this->definitions[] = $definition;
    }

    public function spawn(CustomEntityType $type, Position $position, float $yaw = 0.0, float $pitch = 0.0): void
    {
        $this->spawns[] = [$type, $position, $yaw, $pitch];
    }
}

final class RecordingMobController implements MobController
{
    /** @var list<array{Position, float}> */
    public array $moves = [];

    /** @var list<Position> */
    public array $looks = [];

    /** @var list<array{Entity, float}> */
    public array $targets = [];

    /** @var list<array{float, float, float}> */
    public array $velocities = [];

    public bool $despawned = false;

    public function moveToward(Position $target, float $speed): void
    {
        $this->moves[] = [$target, $speed];
    }

    public function lookAt(Position $target): void
    {
        $this->looks[] = $target;
    }

    public function target(Entity $target, float $speed): void
    {
        $this->targets[] = [$target, $speed];
    }

    public function setVelocity(float $x, float $y, float $z): void
    {
        $this->velocities[] = [$x, $y, $z];
    }

    public function despawn(): void
    {
        $this->despawned = true;
    }
}

final readonly class RecordingMob implements Mob
{
    public function __construct(private EntityType $type) {}

    public function getUniqueId(): string
    {
        return '00000000-0000-4000-8000-000000000001';
    }

    public function getRuntimeId(): int
    {
        return 1;
    }

    public function getType(): EntityType
    {
        return $this->type;
    }

    public function getCategory(): EntityCategory
    {
        return EntityCategory::ANIMAL;
    }

    public function getPosition(): Position
    {
        return new Position(0.0, 64.0, 0.0);
    }

    public function getWorldName(): string
    {
        return 'world';
    }

    public function getYaw(): float
    {
        return 0.0;
    }

    public function getPitch(): float
    {
        return 0.0;
    }

    public function isOnGround(): bool
    {
        return true;
    }

    public function isPersistent(): bool
    {
        return true;
    }

    public function getHealth(): float
    {
        return 10.0;
    }

    public function getMaximumHealth(): float
    {
        return 10.0;
    }

    public function isAlive(): bool
    {
        return true;
    }

    public function getActivationState(): MobActivationState
    {
        return MobActivationState::ACTIVE;
    }
}

final class RecordingRecipeRegistrar implements RecipeRegistrar
{
    /** @var list<ShapedRecipe|ShapelessRecipe> */
    public array $recipes = [];

    public function register(ShapedRecipe|ShapelessRecipe $recipe, bool $replace = false): void
    {
        $this->recipes[] = $recipe;
    }
}

final class RecordingRegistrar implements EventRegistrar
{
    /** @var list<object> */
    public array $subscribers = [];

    public function registerSubscriber(object $subscriber): void
    {
        $this->subscribers[] = $subscriber;
    }
}

final class RecordingLogger implements PluginLogger
{
    /** @var list<string> */
    public array $info = [];
    /** @var list<string> */
    public array $debug = [];

    public function debug(string $message): void
    {
        $this->debug[] = $message;
    }
    public function info(string $message): void
    {
        $this->info[] = $message;
    }
}

final class RecordingCommandRegistrar implements CommandRegistrar
{
    /** @var list<Command> */
    public array $commands = [];

    public function register(Command $command): CommandSubscription
    {
        $this->commands[] = $command;

        return new RecordingCommandSubscription();
    }

    public function registerSoftEnum(string $name, array $values = []): CommandSoftEnum
    {
        throw new RuntimeException('The example plugin does not register a dynamic soft enum.');
    }

    public function submitJob(CommandJob $job): CommandJobSubscription
    {
        throw new RuntimeException('The example command does not submit jobs.');
    }

    public function dispatch(int $index, CommandContext $context): CommandResult
    {
        return $this->commands[$index]->execute($context);
    }
}

final class RecordingCommandSubscription implements CommandSubscription
{
    private bool $registered = true;

    public function unregister(): void
    {
        $this->registered = false;
    }

    public function isRegistered(): bool
    {
        return $this->registered;
    }
}

final class RecordingConsoleSender implements ConsoleCommandSender
{
    /** @var list<string> */
    public array $messages = [];

    public function type(): CommandSenderType
    {
        return CommandSenderType::CONSOLE;
    }

    public function name(): string
    {
        return 'CONSOLE';
    }

    public function sendMessage(string $message): void
    {
        $this->messages[] = $message;
    }

    public function hasPermission(string $permission): bool
    {
        return true;
    }
}

final class RecordingPlayerSender implements PlayerCommandSender
{
    /** @var list<string> */
    public array $messages = [];

    public function __construct(private readonly Player $player) {}

    public function type(): CommandSenderType
    {
        return CommandSenderType::PLAYER;
    }

    public function name(): string
    {
        return $this->player->name;
    }

    public function sendMessage(string $message): void
    {
        $this->messages[] = $message;
    }

    public function hasPermission(string $permission): bool
    {
        return false;
    }

    public function player(): Player
    {
        return $this->player;
    }
}

final class UnusedSourcePluginRegistrar implements SourcePluginRegistrar
{
    public function pluginsDirectory(): string
    {
        return __DIR__;
    }

    /** @param list<SourcePluginDefinition> $definitions */
    public function register(array $definitions): void
    {
        throw new RuntimeException('ExamplePlugin does not register source plugins.');
    }
}

final class RecordingServer implements Server
{
    /** @var list<array{string, string}> */
    public array $messages = [];

    public function sendMessage(Player $player, string $message): void
    {
        $this->messages[] = [$player->uuid, $message];
    }
}
