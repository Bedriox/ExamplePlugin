<?php

declare(strict_types=1);

namespace Bedriox\ExamplePlugin\Command;

use Bedriox\Api\Command\AbstractCommand;
use Bedriox\Api\Command\AllowedCommandSenders;
use Bedriox\Api\Command\CommandContext;
use Bedriox\Api\Command\CommandResult;
use Bedriox\Api\Command\PlayerCommandSender;
use Bedriox\Api\Entity\CustomEntityType;
use Bedriox\Api\Entity\EntityRegistrar;
use Bedriox\Api\World\Position;

final class ExampleSpawnCommand extends AbstractCommand
{
    public function __construct(
        private readonly EntityRegistrar $entities,
        private readonly CustomEntityType $type,
    ) {
        parent::__construct('examplespawn', 'Spawns the ExamplePlugin guide mob.');
    }

    public function execute(CommandContext $context): CommandResult
    {
        $sender = $context->sender();
        if (!$sender instanceof PlayerCommandSender) {
            return $this->failure('This command can only be used by a player.');
        }

        $player = $sender->player();
        $yawRadians = deg2rad($player->yaw);
        $this->entities->spawn(
            $this->type,
            new Position(
                $player->position->x - sin($yawRadians) * 2.0,
                $player->position->y,
                $player->position->z + cos($yawRadians) * 2.0,
            ),
            $player->yaw,
        );

        return $this->success('Spawned the ExamplePlugin guide mob.');
    }

    protected function allowedSenders(): AllowedCommandSenders
    {
        return AllowedCommandSenders::PLAYER_ONLY;
    }
}
