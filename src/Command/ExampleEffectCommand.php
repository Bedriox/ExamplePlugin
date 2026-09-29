<?php

declare(strict_types=1);

namespace Bedriox\ExamplePlugin\Command;

use Bedriox\Api\Command\AbstractCommand;
use Bedriox\Api\Command\AllowedCommandSenders;
use Bedriox\Api\Command\CommandContext;
use Bedriox\Api\Command\CommandResult;
use Bedriox\Api\Command\PlayerCommandSender;
use Bedriox\Api\Effect\EffectInstance;
use Bedriox\Api\Effect\EffectType;
use Bedriox\Api\World\Particle\ParticleType;
use Bedriox\Api\World\Particle\SimpleParticle;

final class ExampleEffectCommand extends AbstractCommand
{
    public function __construct()
    {
        parent::__construct('exampleeffect', 'Applies a short speed effect and displays a particle.');
    }

    public function execute(CommandContext $context): CommandResult
    {
        $sender = $context->sender();
        if (!$sender instanceof PlayerCommandSender) {
            return $this->failure('This command can only be used by a player.');
        }
        $player = $sender->player();
        $world = $player->position->world;
        if ($world === null) {
            return $this->failure('Your world is not available.');
        }

        $player->getEffects()->add(new EffectInstance(
            EffectType::SPEED,
            durationTicks: 200,
        ));
        $world->spawnParticle(
            $player->position,
            new SimpleParticle(ParticleType::HEART),
            [$player],
        );

        return $this->success('Applied Speed for 10 seconds.');
    }

    protected function allowedSenders(): AllowedCommandSenders
    {
        return AllowedCommandSenders::PLAYER_ONLY;
    }
}
