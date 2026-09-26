<?php

declare(strict_types=1);

namespace Bedriox\ExamplePlugin\Entity;

use Bedriox\Api\Entity\CustomEntityState;
use Bedriox\Api\Entity\CustomMobBehavior;
use Bedriox\Api\Entity\CustomMobStateCodec;
use UnexpectedValueException;

final readonly class ExampleMobStateCodec implements CustomMobStateCodec
{
    public const int MAXIMUM_STATE_BYTES = 10;
    public const int MAXIMUM_LIFETIME_TICKS = 2_147_483_647;

    public function encode(CustomMobBehavior $behavior): CustomEntityState
    {
        if (!$behavior instanceof ExampleMobBehavior) {
            throw new UnexpectedValueException('Unexpected custom mob behavior.');
        }

        return new CustomEntityState(1, (string) $behavior->lifetimeTicks());
    }

    public function restore(CustomMobBehavior $behavior, CustomEntityState $state): void
    {
        if (!$behavior instanceof ExampleMobBehavior || $state->schemaVersion !== 1) {
            throw new UnexpectedValueException('Unsupported custom mob state.');
        }

        $payload = $state->bytes();
        if (preg_match('/^(0|[1-9][0-9]{0,9})$/D', $payload) !== 1) {
            throw new UnexpectedValueException('Invalid custom mob lifetime state.');
        }
        $lifetimeTicks = (int) $payload;
        if ($lifetimeTicks > self::MAXIMUM_LIFETIME_TICKS) {
            throw new UnexpectedValueException('Custom mob lifetime state exceeds its supported range.');
        }

        $behavior->restoreLifetimeTicks($lifetimeTicks);
    }
}
