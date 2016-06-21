<?php
namespace DrdPlus\Health;

use DrdPlus\Codes\WoundsOriginCode;
use Granam\Tools\ValueDescriber;

class SeriousWoundOrigin extends WoundOrigin
{
    const MECHANICAL_STAB = WoundsOriginCode::MECHANICAL_STAB;
    const MECHANICAL_CUT = WoundsOriginCode::MECHANICAL_CUT;
    const MECHANICAL_CRUSH = WoundsOriginCode::MECHANICAL_CRUSH;
    const ELEMENTAL = WoundsOriginCode::ELEMENTAL;
    const PSYCHICAL = WoundsOriginCode::PSYCHICAL;

    /**
     * @return SeriousWoundOrigin
     */
    public static function getMechanicalStabWoundOrigin()
    {
        return static::getEnum(WoundsOriginCode::MECHANICAL_STAB);
    }

    /**
     * @return SeriousWoundOrigin
     */
    public static function getMechanicalCutWoundOrigin()
    {
        return static::getEnum(WoundsOriginCode::MECHANICAL_CUT);
    }

    /**
     * @return SeriousWoundOrigin
     */
    public static function getMechanicalCrushWoundOrigin()
    {
        return static::getEnum(WoundsOriginCode::MECHANICAL_CRUSH);
    }

    /**
     * @return SeriousWoundOrigin
     */
    public static function getElementalWoundOrigin()
    {
        return static::getEnum(WoundsOriginCode::ELEMENTAL);
    }

    /**
     * @return SeriousWoundOrigin
     */
    public static function getPsychicalWoundOrigin()
    {
        return static::getEnum(WoundsOriginCode::PSYCHICAL);
    }

    /**
     * @param bool|float|int|string $enumValue
     * @return string
     * @throws \DrdPlus\Health\Exceptions\UnknownWoundOriginCode
     */
    protected static function convertToEnumFinalValue($enumValue)
    {
        $enumFinalValue = parent::convertToEnumFinalValue($enumValue);
        if (!in_array($enumFinalValue, WoundsOriginCode::getWoundsOriginCodes(), true)) {
            throw new Exceptions\UnknownWoundOriginCode(
                'Got unexpected code of wound origin ' . ValueDescriber::describe($enumValue)
            );
        }

        return $enumFinalValue;
    }

    /**
     * @return bool
     */
    public function isSeriousWoundOrigin()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isOrdinaryWoundOrigin()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isMechanical()
    {
        return WoundsOriginCode::getIt($this->getValue())->isMechanical();
    }

    /**
     * @return bool
     */
    public function isMechanicalStabWoundOrigin()
    {
        return $this->getValue() === WoundsOriginCode::MECHANICAL_STAB;
    }

    /**
     * @return bool
     */
    public function isMechanicalCutWoundOrigin()
    {
        return $this->getValue() === WoundsOriginCode::MECHANICAL_CUT;
    }

    /**
     * @return bool
     */
    public function isMechanicalCrushWoundOrigin()
    {
        return $this->getValue() === WoundsOriginCode::MECHANICAL_CRUSH;
    }

    /**
     * @return bool
     */
    public function isElementalWoundOrigin()
    {
        return $this->getValue() === WoundsOriginCode::ELEMENTAL;
    }

    /**
     * @return bool
     */
    public function isPsychicalWoundOrigin()
    {
        return $this->getValue() === WoundsOriginCode::PSYCHICAL;
    }

}