<?php

declare(strict_types=1);

namespace App\Enums;

enum CartSizeType: string
{
    case Standard = 'standard';
    case Custom = 'custom';
}
