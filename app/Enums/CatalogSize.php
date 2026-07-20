<?php

declare(strict_types=1);

namespace App\Enums;

enum CatalogSize: string
{
    case ExtraSmall = 'XS';
    case Small = 'S';
    case Medium = 'M';
    case Large = 'L';
}
