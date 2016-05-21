<?php
namespace DrdPlus\Person\Health;

use DrdPlus\Codes\WoundsOriginCodes;
use Granam\Tools\ValueDescriber;

class SpecificWoundOrigin extends WoundOrigin
{
    /**
     * @return WoundOrigin
     */
    public static function getMechanicalStabWoundOrigin()
    {
        return static::getEnum(WoundsOriginCodes::MECHANICAL_STAB);
    }

    /**
     * @return WoundOrigin
     */
    public static function getMechanicalCutWoundOrigin()
    {
        return static::getEnum(WoundsOriginCodes::MECHANICAL_CUT);
    }

    /**
     * @return WoundOrigin
     */
    public static function getMechanicalCrushWoundOrigin()
    {
        return static::getEnum(WoundsOriginCodes::MECHANICAL_CRUSH);
    }

    /**
     * @return WoundOrigin
     */
    public static function getElementalWoundOrigin()
    {
        return static::getEnum(WoundsOriginCodes::ELEMENTAL);
    }

    /**
     * @return WoundOrigin
     */
    public static function getPsychicalWoundOrigin()
    {
        return static::getEnum(WoundsOriginCodes::PSYCHICAL);
    }

    /**
     * @param bool|float|int|string $enumValue
     * @return string
     * @throws \DrdPlus\Person\Health\Exceptions\UnknownWoundOriginCode
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