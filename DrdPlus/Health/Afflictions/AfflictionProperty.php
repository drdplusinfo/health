<?php
namespace DrdPlus\Health\Afflictions;

use Doctrineum\String\StringEnum;
use DrdPlus\Codes\Properties\PropertyCode;
use Granam\String\StringInterface;
use Granam\Tools\ValueDescriber;

/**
 * @method static AfflictionProperty getEnum($value)
 */
class AfflictionProperty extends StringEnum
{
    /**
     * @param string|StringInterface $propertyCode
     * @return AfflictionProperty
     * @throws \DrdPlus\Health\Afflictions\Exceptions\UnknownAfflictionPropertyCode
     */
    public static function getIt($propertyCode)
    {
        return self::getEnum($propertyCode);
    }

    /**
     * @param string|StringInterface $enumValue
     * @return string
     * @throws \DrdPlus\Health\Afflictions\Exceptions\UnknownAfflictionPropertyCode
     */
    protected static function convertToEnumFinalValue($enumValue)
    {
        $enumFinalValue = parent::convertToEnumFinalValue($enumValue);
        if (!in_array($enumFinalValue, self::getProperties(), true)) {
            throw new Exceptions\UnknownAfflictionPropertyCode(
                'Got unknown code of property keeping affliction on short: '
                . ValueDescriber::describe($enumValue)
            );
        }

        return $enumFinalValue;
    }

    const STRENGTH = PropertyCode::STRENGTH;
    const AGILITY = PropertyCode::AGILITY;
    const KNACK = PropertyCode::KNACK;
    const WILL = PropertyCode::WILL;
    const INTELLIGENCE = PropertyCode::INTELLIGENCE;
    const CHARISMA = PropertyCode::CHARISMA;
    const ENDURANCE = PropertyCode::ENDURANCE;
    const TOUGHNESS = PropertyCode::TOUGHNESS;
    const LEVEL = 'level';

    /**
     * @return array|string[]
     */
    public static function getProperties()
    {
        return [
            self::STRENGTH,
            self::AGILITY,
            self::KNACK,
            self::WILL,
            self::INTELLIGENCE,
            self::CHARISMA,
            self::ENDURANCE,
            self::TOUGHNESS,
            self::LEVEL,
        ];
    }

}