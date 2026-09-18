<?php

declare(strict_types=1);

namespace Bedriox\ExamplePlugin\Tests;

use Bedriox\Api\Command\AllowedCommandSenders;
use Bedriox\Api\Command\CommandContext;
use Bedriox\Api\Command\CommandDefinition;
use Bedriox\Api\Command\CommandJob;
use Bedriox\Api\Command\CommandJobSubscription;
use Bedriox\Api\Command\CommandRegistrar;
use Bedriox\Api\Command\CommandResult;
use Bedriox\Api\Command\CommandSenderType;
use Bedriox\Api\Command\CommandSubscription;
use Bedriox\Api\Command\ConsoleCommandSender;
use Bedriox\Api\Command\PlayerCommandSender;
use Bedriox\Api\Event\EventHandler;
use Bedriox\Api\Event\EventPriority;
use Bedriox\Api\Event\EventRegistrar;
use Bedriox\Api\Event\Player\PlayerChatEvent;
use Bedriox\Api\Event\Player\PlayerJoinEvent;
use Bedriox\Api\Player\Player;
use Bedriox\Api\Plugin\PluginContext;
use Bedriox\Api\Plugin\PluginLogger;
use Bedriox\Api\Plugin\SourcePluginDefinition;
use Bedriox\Api\Plugin\SourcePluginRegistrar;
use Bedriox\Api\Server;
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
        $plugin = new Main(self::context($logger, $registrar, $commands, $server));
        $player = new Player('Alex', 'uuid-one');

        $plugin->onEnable();
        $plugin->onJoin(new PlayerJoinEvent($player));

        self::assertSame([$plugin], $registrar->subscribers);
        self::assertCount(1, $commands->definitions);
        self::assertSame('examplesender', $commands->definitions[0]->name);
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

        $definition = $commands->definitions[0];
        self::assertSame(AllowedCommandSenders::ANY, $definition->allowedSenders);
        self::assertNull($definition->permission);

        $console = new RecordingConsoleSender();
        self::assertSame(
            CommandResult::SUCCESS,
            $commands->dispatch(0, new CommandContext($console, 'examplesender', [])),
        );
        self::assertSame(['This command was sent from the server console.'], $console->messages);

        $player = new RecordingPlayerSender(new Player('Alex', 'uuid-one'));
        self::assertSame(
            CommandResult::SUCCESS,
            $commands->dispatch(0, new CommandContext($player, 'examplesender', [])),
        );
        self::assertSame(['This command was sent by player Alex.'], $player->messages);
    }

    public function testExampleSenderCommandReturnsUsageForArguments(): void
    {
        $commands = new RecordingCommandRegistrar();
        $plugin = new Main(self::context(
            new RecordingLogger(),
            new RecordingRegistrar(),
            $commands,
            new RecordingServer(),
        ));
        $plugin->onEnable();
        $console = new RecordingConsoleSender();

        self::assertSame(
            CommandResult::USAGE,
            $commands->dispatch(0, new CommandContext($console, 'examplesender', ['unexpected'])),
        );
        self::assertSame([], $console->messages);
    }

    public function testAttributesDemonstrateDefaultHighAndMonitorPriorities(): void
    {
        self::assertSame(EventPriority::NORMAL, self::handler('onJoin')->priority);
        self::assertSame(EventPriority::HIGH, self::handler('onChat')->priority);
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
    ): PluginContext {
        return new PluginContext(
            'ExamplePlugin',
            $logger,
            $events,
            $commands,
            new UnusedSourcePluginRegistrar(),
            $server,
            __DIR__ . '/plugin_data/ExamplePlugin',
        );
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
    /** @var list<CommandDefinition> */
    public array $definitions = [];

    /** @var list<callable(CommandContext): CommandResult> */
    private array $handlers = [];

    public function register(CommandDefinition $definition, callable $handler): CommandSubscription
    {
        $this->definitions[] = $definition;
        $this->handlers[] = $handler;

        return new RecordingCommandSubscription();
    }

    public function submitJob(CommandJob $job): CommandJobSubscription
    {
        throw new RuntimeException('The example command does not submit jobs.');
    }

    public function dispatch(int $index, CommandContext $context): CommandResult
    {
        return ($this->handlers[$index])($context);
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
