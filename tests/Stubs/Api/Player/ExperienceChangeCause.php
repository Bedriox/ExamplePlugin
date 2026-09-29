<?php

declare(strict_types=1);

namespace Bedriox\Api\Player;

enum ExperienceChangeCause: string
{
    case COMMAND = 'command';
    case ORB = 'orb';
    case DEATH = 'death';
    case FURNACE = 'furnace';
    case GRINDSTONE = 'grindstone';
    case ENCHANTING = 'enchanting';
    case PLUGIN = 'plugin';
    case OTHER = 'other';
}
