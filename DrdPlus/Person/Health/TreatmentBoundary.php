<?php
namespace DrdPlus\Person\Health;

use Doctrineum\Integer\IntegerEnum;
use Granam\Tools\ValueDescriber;

class TreatmentBoundary extends IntegerEnum
{
    /**
     * @param int $value
     * @return TreatmentBoundary
     * @throws \DrdPlus\Person\Health\Exceptions\TreatmentBoundaryCanNotBeNegative
     */
    public static function getIt($value)
    {
        return static::getEnum($value);
    }

    /**
     * @param bool|float|int|object|string $enumValue
     * @throws \DrdPlus\Person\Health\Exceptions\TreatmentBoundaryCanNotBeNegative
     * @return int
     */
    protected static function convertToEnumFinalValue($enumValue)
    {
        try {
            $finalValue = parent::convertToEnumFinalValue($enumValue);
        } catch (\Doctrineum\Integer\Exceptions\Exception $conversionException) {
            throw new Exceptions\TreatmentBoundaryCanNotBeNegative(
                'Expected integer as a wound value, got ' . ValueDescriber::describe($enumValue),
                $conversionException->getCode(),
                $conversionException
            );
        }

        if ($finalValue < 0) {
            throw new Exceptions\TreatmentBoundaryCanNotBeNegative(
                'Expected at least zero, got ' . ValueDescriber::describe($enumValue)
            );
        }

        return $finalValue;
    }
}