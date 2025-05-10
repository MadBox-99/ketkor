<?php

declare(strict_types=1);

namespace App\Enums;

enum ProductLogType: string
{
    const Maintenance = 'maintenance';

    const Installation = 'installation';
}
