<?php

declare(strict_types=1);

namespace Bedriox\ExamplePlugin\Command;

use Bedriox\Api\Command\AbstractCommand;
use Bedriox\Api\Command\CommandContext;
use Bedriox\Api\Command\CommandResult;
use Bedriox\Api\Command\ConsoleCommandSender;
use Bedriox\Api\Command\PlayerCommandSender;

final class ExampleSenderCommand extends AbstractCommand
{
    public function __construct()
    {
        parent::__construct('examplesender', 'Shows whether a command came from the console or a player.');
    }

    public function execute(CommandContext $context): CommandResult
    {
        $sender = $context->sender();
        if ($sender instanceof PlayerCommandSender) {
            $sender->sendMessage('This command was sent by player ' . $sender->player()->name . '.');

            return $this->success();
        }
        if ($sender instanceof ConsoleCommandSender) {
            $sender->sendMessage('This command was sent from the server console.');

            return $this->success();
        }

        return $this->failure('This command sender is not supported.');
    }
}
