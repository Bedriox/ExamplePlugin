<?php

declare(strict_types=1);

namespace Bedriox\ExamplePlugin\Command;

use Bedriox\Api\Command\AbstractCommand;
use Bedriox\Api\Command\CommandArguments;
use Bedriox\Api\Command\CommandContext;
use Bedriox\Api\Command\CommandParameter;
use Bedriox\Api\Command\CommandResult;
use Bedriox\Api\Server;

final class ExampleBroadcastCommand extends AbstractCommand
{
    public function __construct(private readonly Server $server)
    {
        parent::__construct('examplebroadcast', 'Broadcasts a message to every connected player.');
    }

    public function defineArguments(): CommandArguments
    {
        return CommandArguments::create()->addArgument(CommandParameter::message('message'));
    }

    public function execute(CommandContext $context): CommandResult
    {
        $recipients = $this->server->broadcastMessage($context->values()->message('message'));

        return $this->success("Sent the message to {$recipients} player(s).");
    }
}
