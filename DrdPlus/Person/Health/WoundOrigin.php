<?php
namespace DrdPlus\Person\Health;

use Doctrineum\String\StringEnum;
use DrdPlus\Codes\WoundsOriginCodes;
use Granam\Tools\ValueDescriber;

class WoundOrigin extends StringEnum
{
    /**
     * @return WoundOrigin
     */
    public static function getMechanicalStabWoundOrigin()
    {
        return static::getEnum(WoundsOriginCodes::MECHANICAL_STAB);
    }

    /**
     * @return bool
     */
    public function isMechanicalStabWoundOrigin()
    {
        return $this->is(self::getMechanicalStabWoundOrigin());
    }

    /**
     * @return WoundOrigin
     */
    public static function getMechanicalCutWoundOrigin()
    {
        return static::getEnum(WoundsOriginCodes::MECHANICAL_CUT);
    }

    /**
     * @return bool
     */
    public function isMechanicalCutWoundOrigin()
    {
        return $this->is(self::getMechanicalCutWoundOrigin());
    }

    /**
     * @return WoundOrigin
     */
    public static function getMechanicalCrushWoundOrigin()
    {
        return static::getEnum(WoundsOriginCodes::MECHANICAL_CRUSH);
    }

    /**
     * @return bool
     */
    public function isMechanicalCrushWoundOrigin()
    {
        return $this->is(self::getMechanicalCrushWoundOrigin());
    }

    /**
     * @return WoundOrigin
     */
    public static function getElementalWoundOrigin()
    {
        return static::getEnum(WoundsOriginCodes::ELEMENTAL);
    }

    /**
     * @return bool
     */
    public function isElementalWoundOrigin()
    {
        return $this->is(self::getElementalWoundOrigin());
    }

    /**
     * @return WoundOrigin
     */
    public static function getPsychicalWoundOrigin()
    {
        return static::getEnum(WoundsOriginCodes::PSYCHICAL);
    }

    /**
     * @return bool
     */
    public function isPsychicalWoundOrigin()
    {
        return $this->is(self::getPsychicalWoundOrigin());
    }

    /**
     * @return bool
     */
    public function isExtraOrdinaryWoundOrigin()
    {
        return !$this->isOrdinaryWoundOrigin();
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
     * @return bool
     */
    public function isOrdinaryWoundOrigin()
    {
        return $this->is(self::getOrdinaryWoundOrigin());
    }

    /**
     * @param bool|float|int|string $enumValue
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