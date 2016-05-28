<?php
namespace DrdPlus\Person\Health;

use Doctrineum\String\StringEnum;
use Granam\Tools\ValueDescriber;

class ReasonToRollAgainstMalus extends StringEnum
{
    const WOUND = 'wound';

    /**
     * @return ReasonToRollAgainstMalus
     */
    public static function getWoundReason()
    {
        return static::getEnum(self::WOUND);
    }

    public function becauseOfWound()
    {
        return $this->getValue() === self::WOUND;
    }

    const HEAL = 'heal';

    /**
     * @return ReasonToRollAgainstMalus
     */
    public static function getHealReason()
    {
        return static::getEnum(self::HEAL);
    }

    public function becauseOfHeal()
    {
        return $this->getValue() === self::HEAL;
    }

    /**
     * @param string $reasonCode
     * @return ReasonToRollAgainstMalus
     * @throws \DrdPlus\Person\Health\Exceptions\UnknownReasonToRollAgainstMalus
     */
    public static function getIt($reasonCode)
    {
        return static::getEnum($reasonCode);
    }

    /**
     * @param bool|float|int|object|string $enumValue
     * @return string
     * @throws \DrdPlus\Person\Health\Exceptions\UnknownReasonToRollAgainstMalus
     */
    protected static function convertToEnumFinalValue($enumValue)
    {
        $finalValue = parent::convertToEnumFinalValue($enumValue);
        if ($finalValue !== self::WOUND && $finalValue !== self::HEAL) {
            throw new Exceptions\UnknownReasonToRollAgainstMalus(
                'Expected one of ' . self::WOUND . ' or ' . self::HEAL . ', got ' . ValueDescriber::describe($enumValue)
            );
        }

        return $finalValue;
    }

}