<?php
namespace DrdPlus\Health\Afflictions;

use Doctrineum\String\StringEnum;
use Granam\Tools\ValueDescriber;

/**
 * @method static AfflictionName getEnum($value)
 */
class AfflictionName extends StringEnum
{
    /**
     * @param string $nameValue
     * @return AfflictionName
     */
    public static function getIt($nameValue): AfflictionName
    {
        return self::getEnum($nameValue);
    }

    /**
     * @param bool|float|int|string $enumValue
     * @return string
     * @throws \DrdPlus\Health\Afflictions\Exceptions\AfflictionNameCanNotBeEmpty
     * @throws \Doctrineum\String\Exceptions\UnexpectedValueToEnum
     */
    protected static function convertToEnumFinalValue($enumValue): string
    {
        $finalValue = parent::convertToEnumFinalValue($enumValue);
        if ($finalValue === '') {
            throw new Exceptions\AfflictionNameCanNotBeEmpty(
                'Name of an affliction has to have some value, got ' . ValueDescriber::describe($enumValue)
            );
        }

        return $finalValue;
    }

}