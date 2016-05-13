<?php
namespace DrdPlus\Person\Health;

use Doctrineum\Integer\IntegerEnum;

class TreatmentBoundary extends IntegerEnum
{
    /**
     * @param int $value
     * @return TreatmentBoundary
     */
    public static function getIt($value)
    {
        return static::getEnum($value);
    }
}