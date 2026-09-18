<?php

declare(strict_types=1);

namespace Bedriox\ExamplePlugin\Tests;

use Bedriox\Api\Event\EventHandler;
use Bedriox\Api\Event\EventPriority;
use Bedriox\Api\Event\EventRegistrar;
use Bedriox\Api\Event\Player\PlayerChatEvent;
use Bedriox\Api\Event\Player\PlayerJoinEvent;
use Bedriox\Api\Player\Player;
use Bedriox\Api\Plugin\PluginContext;
use Bedriox\Api\Plugin\PluginLogger;
use Bedriox\Api\Server;
use Bedriox\ExamplePlugin\Main;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class ExamplePluginTest extends TestCase
{
    public function testLifecycleRegistersSubscriberAndGreetsJoiningPlayer(): void
    {
        $registrar = new RecordingRegistrar();
        $logger = new RecordingLogger();
        $server = new RecordingServer();
        $plugin = new Main(new PluginContext($logger, $registrar, $server));
        $player = new Player('Alex', 'uuid-one');

        $plugin->onEnable();
        $plugin->onJoin(new PlayerJoinEvent($player));

        self::assertSame([$plugin], $registrar->subscribers);
        self::assertSame(['ExamplePlugin enabled'], $logger->info);
        self::assertSame([['uuid-one', 'Welcome to this Bedriox server, Alex!']], $server->messages);
    }

    public function testChatExampleCancelsOnlyTheDocumentedPhrase(): void
    {
        $server = new RecordingServer();
        $plugin = new Main(new PluginContext(new RecordingLogger(), new RecordingRegistrar(), $server));
        $player = new Player('Alex', 'uuid-one');
        $accepted = new PlayerChatEvent($player, 'hello');
        $cancelled = new PlayerChatEvent($player, '  Cancel Me  ');

        $plugin->onChat($accepted);
        $plugin->onChat($cancelled);

        self::assertFalse($accepted->isCancelled());
        self::assertTrue($cancelled->isCancelled());
        self::assertSame([['uuid-one', 'ExamplePlugin cancelled that message.']], $server->messages);
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

final class RecordingServer implements Server
{
    /** @var list<array{string, string}> */
    public array $messages = [];

    public function sendMessage(Player $player, string $message): void
    {
        $this->messages[] = [$player->uuid, $message];
    }
}
