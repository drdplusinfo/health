<?php
namespace DrdPlus\Person\Health\Afflictions;

use Doctrineum\Integer\IntegerEnum;

class AfflictionDangerousness extends IntegerEnum
{
    /**
     * @param $value
     * @return AfflictionDangerousness
     */
    public static function getIt($value)
    {
        return static::getEnum($value);
    }
}