<?php
namespace DrdPlus\Person\Health;

use Doctrineum\Integer\IntegerEnum;
use Granam\Tools\ValueDescriber;

class MalusFromWounds extends IntegerEnum
{
    /**
     * @param int $malusValue
     * @return MalusFromWounds
     * @throws \DrdPlus\Person\Health\Exceptions\UnexpectedMalusValue
     * @throws \Doctrineum\Integer\Exceptions\UnexpectedValueToConvert
     */
    public static function getIt($malusValue)
    {
        return static::getEnum($malusValue);
    }

    const LEAST = 0;
    const MOST = -3;

    /**
     * @param mixed $enumValue
     * @return int
     * @throws \DrdPlus\Person\Health\Exceptions\UnexpectedMalusValue
     * @throws \Doctrineum\Integer\Exceptions\UnexpectedValueToConvert
     */
    protected static function convertToEnumFinalValue($enumValue)
    {
        $finalValue = parent::convertToEnumFinalValue($enumValue);
        if ($finalValue > self::LEAST || $finalValue < self::MOST) { // note: comparing negative numbers
            throw new Exceptions\UnexpectedMalusValue(
                'Malus can be between ' . self::LEAST . ' and ' . self::MOST . ', got ' . ValueDescriber::describe($enumValue)
            );
        }

        return $finalValue;
    }

}