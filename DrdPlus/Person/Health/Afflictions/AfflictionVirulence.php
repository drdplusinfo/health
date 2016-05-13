<?php
namespace DrdPlus\Person\Health\Afflictions;

use Doctrineum\String\StringEnum;
use DrdPlus\Tables\Measurements\Time\Time;
use Granam\Tools\ValueDescriber;

class AfflictionVirulence extends StringEnum
{
    const VIRULENCE = 'virulence';

    /**
     * @return AfflictionVirulence
     */
    public static function getRoundVirulence()
    {
        return static::getEnum(Time::ROUND);
    }

    /**
     * @return AfflictionVirulence
     */
    public static function getMinuteVirulence()
    {
        return static::getEnum(Time::MINUTE);
    }

    /**
     * @return AfflictionVirulence
     */
    public static function getHourVirulence()
    {
        return static::getEnum(Time::HOUR);
    }

    /**
     * @return AfflictionVirulence
     */
    public static function getDayVirulence()
    {
        return static::getEnum(Time::DAY);
    }

    /**
     * @param bool|float|int|string $enumValue
     * @return string
     * @throws \DrdPlus\Person\Health\Afflictions\Exceptions\UnknownVirulencePeriod
     */
    protected static function convertToEnumFinalValue($enumValue)
    {
        $finalValue = parent::convertToEnumFinalValue($enumValue);
        if (!in_array($finalValue, [Time::ROUND, Time::MINUTE, Time::HOUR, Time::DAY], true)) {
            throw new Exceptions\UnknownVirulencePeriod(
                'Unknown period of a virulence: ' . ValueDescriber::describe($enumValue)
            );
        }

        return $finalValue;
    }

}