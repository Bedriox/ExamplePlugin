<?php

declare(strict_types=1);

namespace Bedriox\Api\Command;

use BackedEnum;
use LogicException;

final readonly class CommandValues
{
    /** @param array<string, mixed> $values */
    public function __construct(private array $values = []) {}

    /**
     * @template T of BackedEnum
     * @param class-string<T> $enumClass
     * @return T
     */
    public function enum(string $name, string $enumClass): BackedEnum
    {
        $value = $this->values[$name] ?? null;
        if (!$value instanceof $enumClass) {
            throw new LogicException('Command enum value is missing.');
        }

        return $value;
    }
}
