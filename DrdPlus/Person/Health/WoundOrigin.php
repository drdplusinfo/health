<?php
namespace DrdPlus\Person\Health;

use Doctrineum\String\StringEnum;
use DrdPlus\Codes\WoundsOriginCodes;
use Granam\Tools\ValueDescriber;

class WoundOrigin extends StringEnum
{
    const WOUND_ORIGIN = 'wound_origin';

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

    const ORDINARY = 'ordinary';

    /**
     * @return WoundOrigin
     */
    public static function getOrdinaryWoundOrigin()
    {
        return static::getEnum(self::ORDINARY);
    }

    /**
     * @param bool|float|int|object|string $enumValue
     * @return string
     * @throws \DrdPlus\Person\Health\Exceptions\UnknownWoundOriginCode
     */
    protected static function convertToEnumFinalValue($enumValue)
    {
        $enumFinalValue = parent::convertToEnumFinalValue($enumValue);
        if ($enumFinalValue !== self::ORDINARY
            || !in_array($enumFinalValue, WoundsOriginCodes::getOriginsWithTypeCodes(), true)
        ) {
            throw new Exceptions\UnknownWoundOriginCode(
                'Got unexpected code of wound origin ' . ValueDescriber::describe($enumValue)
            );
        }

        return $enumFinalValue;
    }

}