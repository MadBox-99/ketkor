<?php

declare(strict_types=1);

namespace App\Enums;

enum ProductLogType: string
{
    public const Maintenance = 'maintenance';

    public const Installation = 'installation';
}
