<?php

declare(strict_types=1);

namespace Bedriox\Api\Processing;

enum FurnaceType: string
{
    case FURNACE = 'furnace';
    case BLAST_FURNACE = 'blast_furnace';
    case SMOKER = 'smoker';
}
