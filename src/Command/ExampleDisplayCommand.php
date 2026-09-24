<?php

declare(strict_types=1);

namespace Bedriox\ExamplePlugin\Command;

use Bedriox\Api\Command\AbstractCommand;
use Bedriox\Api\Command\AllowedCommandSenders;
use Bedriox\Api\Command\CommandArguments;
use Bedriox\Api\Command\CommandContext;
use Bedriox\Api\Command\CommandParameter;
use Bedriox\Api\Command\CommandResult;
use Bedriox\Api\Command\PlayerCommandSender;
use Bedriox\Api\Player\TitleTimes;

final class ExampleDisplayCommand extends AbstractCommand
{
    public function __construct()
    {
        parent::__construct('exampledisplay', 'Demonstrates player text and display methods.');
    }

    public function defineArguments(): CommandArguments
    {
        return CommandArguments::create()
            ->addArgument(CommandParameter::enum('display', DisplayMode::class));
    }

    public function execute(CommandContext $context): CommandResult
    {
        $sender = $context->sender();
        if (!$sender instanceof PlayerCommandSender) {
            return $this->failure('This command can only be used by a player.');
        }
        $player = $sender->player();
        $sent = match ($context->values()->enum('display', DisplayMode::class)) {
            DisplayMode::MESSAGE => $player->sendMessage('Example message'),
            DisplayMode::POPUP => $player->sendPopup('Example popup'),
            DisplayMode::JUKEBOX => $player->sendJukeboxPopup('Example jukebox popup'),
            DisplayMode::TIP => $player->sendTip('Example tip'),
            DisplayMode::TITLE => $player->sendTitle('Example title', 'Example subtitle', new TitleTimes(10, 70, 20)),
            DisplayMode::SUBTITLE => $player->sendSubTitle('Example subtitle'),
            DisplayMode::ACTIONBAR => $player->sendActionBar('Example action bar'),
            DisplayMode::TOAST => $player->sendToast('Example toast', 'Player display API is working'),
            DisplayMode::CLEAR => $player->clearTitle(),
            DisplayMode::RESET => $player->resetTitles(),
        };

        return $sent ? $this->success() : $this->failure('The display request could not be queued.');
    }

    protected function allowedSenders(): AllowedCommandSenders
    {
        return AllowedCommandSenders::PLAYER_ONLY;
    }
}
