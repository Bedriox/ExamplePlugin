<?php

declare(strict_types=1);

namespace Bedriox\Api;

final class TextFormat
{
    public const string ESCAPE = "\u{00a7}";
    public const string YELLOW = self::ESCAPE . 'e';
    public const string RESET = self::ESCAPE . 'r';

    private function __construct() {}
}
