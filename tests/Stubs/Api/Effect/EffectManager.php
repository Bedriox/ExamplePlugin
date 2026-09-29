<?php

declare(strict_types=1);

namespace Bedriox\Api\Effect;

final class EffectManager
{
    /** @var list<array{EffectInstance, EffectCause}> */
    public array $added = [];

    public function add(EffectInstance $effect, EffectCause $cause = EffectCause::PLUGIN): void
    {
        $this->added[] = [$effect, $cause];
    }
}
