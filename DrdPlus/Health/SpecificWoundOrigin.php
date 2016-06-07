<?php
namespace DrdPlus\Health;

use DrdPlus\Codes\WoundsOriginCodes;
use Granam\Tools\ValueDescriber;

class SpecificWoundOrigin extends WoundOrigin
{
    const MECHANICAL_STAB = WoundsOriginCodes::MECHANICAL_STAB;
    const MECHANICAL_CUT = WoundsOriginCodes::MECHANICAL_CUT;
    const MECHANICAL_CRUSH = WoundsOriginCodes::MECHANICAL_CRUSH;
    const ELEMENTAL = WoundsOriginCodes::ELEMENTAL;
    const PSYCHICAL = WoundsOriginCodes::PSYCHICAL;

    /**
     * @return SpecificWoundOrigin
     */
    public static function getMechanicalStabWoundOrigin()
    {
        return static::getEnum(WoundsOriginCodes::MECHANICAL_STAB);
    }

    /**
     * @return SpecificWoundOrigin
     */
    public static function getMechanicalCutWoundOrigin()
    {
        return static::getEnum(WoundsOriginCodes::MECHANICAL_CUT);
    }

    /**
     * @return SpecificWoundOrigin
     */
    public static function getMechanicalCrushWoundOrigin()
    {
        return static::getEnum(WoundsOriginCodes::MECHANICAL_CRUSH);
    }

    /**
     * @return SpecificWoundOrigin
     */
    public static function getElementalWoundOrigin()
    {
        return static::getEnum(WoundsOriginCodes::ELEMENTAL);
    }

    /**
     * @return SpecificWoundOrigin
     */
    public static function getPsychicalWoundOrigin()
    {
        return static::getEnum(WoundsOriginCodes::PSYCHICAL);
    }

    /**
     * @param bool|float|int|string $enumValue
     * @return string
     * @throws \DrdPlus\Health\Exceptions\UnknownWoundOriginCode
     */
    protected static function convertToEnumFinalValue($enumValue)
    {
        $enumFinalValue = parent::convertToEnumFinalValue($enumValue);
        if (!in_array($enumFinalValue, WoundsOriginCodes::getOriginWithTypeCodes(), true)
        ) {
            throw new Exceptions\UnknownWoundOriginCode(
                'Got unexpected code of wound origin ' . ValueDescriber::describe($enumValue)
            );
        }

        return $enumFinalValue;
    }
}