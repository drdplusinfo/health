<?php
namespace DrdPlus\Health;

use DrdPlus\Codes\Body\WoundsOriginCode;
use Granam\Tools\ValueDescriber;

/**
 * @method static SeriousWoundOrigin getEnum($value)
 */
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
    public static function getMechanicalStabWoundOrigin(): SeriousWoundOrigin
    {
        return static::getEnum(WoundsOriginCode::MECHANICAL_STAB);
    }

    /**
     * @return SeriousWoundOrigin
     */
    public static function getMechanicalCutWoundOrigin(): SeriousWoundOrigin
    {
        return static::getEnum(WoundsOriginCode::MECHANICAL_CUT);
    }

    /**
     * @return SeriousWoundOrigin
     */
    public static function getMechanicalCrushWoundOrigin(): SeriousWoundOrigin
    {
        return static::getEnum(WoundsOriginCode::MECHANICAL_CRUSH);
    }

    /**
     * @return SeriousWoundOrigin
     */
    public static function getElementalWoundOrigin(): SeriousWoundOrigin
    {
        return static::getEnum(WoundsOriginCode::ELEMENTAL);
    }

    /**
     * @return SeriousWoundOrigin
     */
    public static function getPsychicalWoundOrigin(): SeriousWoundOrigin
    {
        return static::getEnum(WoundsOriginCode::PSYCHICAL);
    }

    /**
     * @param bool|float|int|string $enumValue
     * @return string
     * @throws \DrdPlus\Health\Exceptions\UnknownWoundOriginCode
     */
    protected static function convertToEnumFinalValue($enumValue): string
    {
        $enumFinalValue = parent::convertToEnumFinalValue($enumValue);
        if (!in_array($enumFinalValue, WoundsOriginCode::getPossibleValues(), true)) {
            throw new Exceptions\UnknownWoundOriginCode(
                'Got unexpected code of wound origin ' . ValueDescriber::describe($enumValue)
            );
        }

        return $enumFinalValue;
    }

    /**
     * @return bool
     */
    public function isSeriousWoundOrigin(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isOrdinaryWoundOrigin(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isMechanical(): bool
    {
        return WoundsOriginCode::getIt($this->getValue())->isMechanical();
    }

    /**
     * @return bool
     */
    public function isMechanicalStabWoundOrigin(): bool
    {
        return $this->getValue() === WoundsOriginCode::MECHANICAL_STAB;
    }

    /**
     * @return bool
     */
    public function isMechanicalCutWoundOrigin(): bool
    {
        return $this->getValue() === WoundsOriginCode::MECHANICAL_CUT;
    }

    /**
     * @return bool
     */
    public function isMechanicalCrushWoundOrigin(): bool
    {
        return $this->getValue() === WoundsOriginCode::MECHANICAL_CRUSH;
    }

    /**
     * @return bool
     */
    public function isElementalWoundOrigin(): bool
    {
        return $this->getValue() === WoundsOriginCode::ELEMENTAL;
    }

    /**
     * @return bool
     */
    public function isPsychicalWoundOrigin(): bool
    {
        return $this->getValue() === WoundsOriginCode::PSYCHICAL;
    }

}