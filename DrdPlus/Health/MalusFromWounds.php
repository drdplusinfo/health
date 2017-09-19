<?php
namespace DrdPlus\Health;

use Doctrineum\Integer\IntegerEnum;
use Granam\Tools\ValueDescriber;

/**
 * @method static MalusFromWounds getEnum($value)
 */
class MalusFromWounds extends IntegerEnum
{
    const MOST = -3;

    /**
     * @param int $malusValue
     * @return MalusFromWounds
     * @throws \DrdPlus\Health\Exceptions\UnexpectedMalusValue
     * @throws \Doctrineum\Integer\Exceptions\UnexpectedValueToConvert
     */
    public static function getIt($malusValue): MalusFromWounds
    {
        return static::getEnum($malusValue);
    }

    /**
     * @param mixed $enumValue
     * @return int
     * @throws \DrdPlus\Health\Exceptions\UnexpectedMalusValue
     * @throws \Doctrineum\Integer\Exceptions\UnexpectedValueToConvert
     */
    protected static function convertToEnumFinalValue($enumValue): int
    {
        $finalValue = parent::convertToEnumFinalValue($enumValue);
        if ($finalValue > 0 // note: comparing negative numbers
            || $finalValue < self::MOST
        ) {
            throw new Exceptions\UnexpectedMalusValue(
                'Malus can be between 0 and ' . self::MOST . ', got ' . ValueDescriber::describe($enumValue)
            );
        }

        return $finalValue;
    }

}