<?php

declare(strict_types=1);

namespace Bedriox\ExamplePlugin\Command;

enum DisplayMode: string
{
    case MESSAGE = 'message';
    case POPUP = 'popup';
    case JUKEBOX = 'jukebox';
    case TIP = 'tip';
    case TITLE = 'title';
    case SUBTITLE = 'subtitle';
    case ACTIONBAR = 'actionbar';
    case TOAST = 'toast';
    case CLEAR = 'clear';
    case RESET = 'reset';
}
