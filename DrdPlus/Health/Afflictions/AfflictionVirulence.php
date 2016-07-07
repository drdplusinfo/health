<?php
namespace DrdPlus\Health\Afflictions;

use Doctrineum\String\StringEnum;
use DrdPlus\Codes\TimeCode;
use Granam\Tools\ValueDescriber;

/**
 * @method static AfflictionVirulence getEnum($enumValue)
 */
class AfflictionVirulence extends StringEnum
{
    const AFFLICTION_VIRULENCE = 'affliction_virulence';

    const ROUND = TimeCode::ROUND;

    /**
     * @return AfflictionVirulence
     */
    public static function getRoundVirulence()
    {
        return static::getEnum(self::ROUND);
    }

    const MINUTE = TimeCode::MINUTE;

    /**
     * @return AfflictionVirulence
     */
    public static function getMinuteVirulence()
    {
        return static::getEnum(TimeCode::MINUTE);
    }

    const HOUR = TimeCode::HOUR;

    /**
     * @return AfflictionVirulence
     */
    public static function getHourVirulence()
    {
        return static::getEnum(self::HOUR);
    }

    const DAY = TimeCode::DAY;

    /**
     * @return AfflictionVirulence
     */
    public static function getDayVirulence()
    {
        return static::getEnum(self::DAY);
    }

    /**
     * @param bool|float|int|string $enumValue
     * @return string
     * @throws \DrdPlus\Health\Afflictions\Exceptions\UnknownVirulencePeriod
     */
    protected static function convertToEnumFinalValue($enumValue)
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