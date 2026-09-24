<?php

declare(strict_types=1);

namespace Bedriox\Api\Command;

final class CommandArguments
{
    /** @param list<CommandParameter> $parameters */
    private function __construct(private array $parameters = []) {}

    public static function create(): self
    {
        return new self();
    }

    public static function none(): self
    {
        return new self();
    }

    public function addArgument(CommandParameter $parameter): self
    {
        $arguments = clone $this;
        $arguments->parameters[] = $parameter;

        return $arguments;
    }

    /** @return list<CommandParameter> */
    public function parameters(): array
    {
        return $this->parameters;
    }
}
