<?php

declare(strict_types=1);

namespace Bedriox\Api\Event;

interface EventRegistrar
{
    public function registerSubscriber(object $subscriber): void;
}
