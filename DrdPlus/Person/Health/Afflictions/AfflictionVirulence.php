<?php
namespace DrdPlus\Person\Health\Afflictions;

use Doctrineum\String\StringEnum;
use DrdPlus\Codes\TimeCodes;
use Granam\Tools\ValueDescriber;

class AfflictionVirulence extends StringEnum
{
    const VIRULENCE = 'virulence';

    const ROUND = TimeCodes::ROUND;

    /**
     * @return AfflictionVirulence
     */
    public static function getRoundVirulence()
    {
        return static::getEnum(self::ROUND);
    }

    const MINUTE = TimeCodes::MINUTE;

    /**
     * @return AfflictionVirulence
     */
    public static function getMinuteVirulence()
    {
        return static::getEnum(TimeCodes::MINUTE);
    }

    const HOUR = TimeCodes::HOUR;

    /**
     * @return AfflictionVirulence
     */
    public static function getHourVirulence()
    {
        return static::getEnum(self::HOUR);
    }

    const DAY = TimeCodes::DAY;

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
     * @throws \DrdPlus\Person\Health\Afflictions\Exceptions\UnknownVirulencePeriod
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