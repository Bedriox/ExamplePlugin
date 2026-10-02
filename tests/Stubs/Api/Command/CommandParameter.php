<?php

declare(strict_types=1);

namespace Bedriox\Api\Command;

use BackedEnum;

final readonly class CommandParameter
{
    /**
     * @param list<string>             $choices
     * @param class-string<BackedEnum>|null $enumClass
     */
    private function __construct(
        public string $name,
        public array $choices,
        public ?string $enumClass,
    ) {}

    /** @param class-string<BackedEnum> $enumClass */
    public static function enum(string $name, string $enumClass): self
    {
        return new self(
            $name,
            array_map(static fn(BackedEnum $case): string => (string) $case->value, $enumClass::cases()),
            $enumClass,
        );
    }

    public static function message(string $name): self
    {
        return new self($name, [], null);
    }
}
