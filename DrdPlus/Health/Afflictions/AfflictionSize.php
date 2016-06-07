<?php
namespace DrdPlus\Health\Afflictions;

use Doctrineum\Integer\IntegerEnum;
use Granam\Tools\ValueDescriber;

class AfflictionSize extends IntegerEnum
{
    /**
     * @param int $size
     * @return AfflictionSize
     * @throws \DrdPlus\Health\Afflictions\Exceptions\AfflictionSizeCanNotBeNegative
     * @throws \Doctrineum\Integer\Exceptions\UnexpectedValueToConvert
     */
    public static function getIt($size)
    {
        return self::getEnum($size);
    }

    /**
     * @param mixed $enumValue
     * @return int
     * @throws \DrdPlus\Health\Afflictions\Exceptions\AfflictionSizeCanNotBeNegative
     * @throws \Doctrineum\Integer\Exceptions\UnexpectedValueToConvert
     */
    protected static function convertToEnumFinalValue($enumValue)
    {
        $finalValue = parent::convertToEnumFinalValue($enumValue);
        if ($finalValue < 0) {
            throw new Exceptions\AfflictionSizeCanNotBeNegative(
                'Affliction size has to be at least 0, got ' . ValueDescriber::describe($enumValue)
            );
        }

        return $finalValue;
    }

}