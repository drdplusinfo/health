<?php
namespace DrdPlus\Health\Afflictions;

use Doctrineum\Integer\IntegerEnum;

/**
 * @method static getEnum($value)
 */
class AfflictionDangerousness extends IntegerEnum
{
    /**
     * @param $value
     * @return AfflictionDangerousness
     */
    public static function getIt($value): AfflictionDangerousness
    {
        return static::getEnum($value);
    }
}