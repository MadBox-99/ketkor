<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static MAINTENANCE()
 * @method static static INSTALLATION()
 * @method static static OptionThree()
 */
final class ProductLogType extends Enum
{
    const Maintenance = 'maintenance';
    const Installation = 'installation';
}
