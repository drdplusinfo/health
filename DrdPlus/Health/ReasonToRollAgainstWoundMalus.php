<?php
namespace DrdPlus\Health;

use Doctrineum\String\StringEnum;
use Granam\Scalar\ScalarInterface;
use Granam\Tools\ValueDescriber;

/**
 * @method static ReasonToRollAgainstWoundMalus getEnum($value)
 */
class ReasonToRollAgainstWoundMalus extends StringEnum
{
    const WOUND = 'wound';

    /**
     * @return ReasonToRollAgainstWoundMalus
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
     * @return ReasonToRollAgainstWoundMalus
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
     * @return ReasonToRollAgainstWoundMalus
     * @throws \DrdPlus\Health\Exceptions\UnknownReasonToRollAgainstMalus
     */
    public static function getIt($reasonCode)
    {
        return static::getEnum($reasonCode);
    }

    /**
     * @param bool|float|int|ScalarInterface|string $enumValue
     * @return string
     * @throws \DrdPlus\Health\Exceptions\UnknownReasonToRollAgainstMalus
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