<?php
namespace DrdPlus\Health\Afflictions;

use Doctrineum\String\StringEnum;
use DrdPlus\Codes\Units\TimeUnitCode;
use Granam\Tools\ValueDescriber;

/**
 * @method static AfflictionVirulence getEnum($enumValue)
 */
class AfflictionVirulence extends StringEnum
{
    const AFFLICTION_VIRULENCE = 'affliction_virulence';

    const ROUND = TimeUnitCode::ROUND;

    /**
     * @return AfflictionVirulence
     */
    public static function getRoundVirulence(): AfflictionVirulence
    {
        return static::getEnum(self::ROUND);
    }

    const MINUTE = TimeUnitCode::MINUTE;

    /**
     * @return AfflictionVirulence
     */
    public static function getMinuteVirulence(): AfflictionVirulence
    {
        return static::getEnum(TimeUnitCode::MINUTE);
    }

    const HOUR = TimeUnitCode::HOUR;

    /**
     * @return AfflictionVirulence
     */
    public static function getHourVirulence(): AfflictionVirulence
    {
        return static::getEnum(self::HOUR);
    }

    const DAY = TimeUnitCode::DAY;

    /**
     * @return AfflictionVirulence
     */
    public static function getDayVirulence(): AfflictionVirulence
    {
        return static::getEnum(self::DAY);
    }

    /**
     * @param bool|float|int|string|object $enumValue
     * @return string
     * @throws \DrdPlus\Health\Afflictions\Exceptions\UnknownVirulencePeriod
     * @throws \Doctrineum\String\Exceptions\UnexpectedValueToEnum
     */
    protected static function convertToEnumFinalValue($enumValue): string
    {
        $finalValue = parent::convertToEnumFinalValue($enumValue);
        if (!in_array($finalValue, [self::ROUND, self::MINUTE, self::HOUR, self::DAY], true)) {
            throw new Exceptions\UnknownVirulencePeriod(
                'Unknown period of a virulence: ' . ValueDescriber::describe($enumValue)
            );
        }

        return $finalValue;
    }

}