<?php
namespace DrdPlus\Person\Health;

use Doctrineum\String\StringEnum;
use DrdPlus\Codes\WoundsOriginCodes;

abstract class WoundOrigin extends StringEnum
{
    /**
     * @return bool
     */
    public function isMechanical()
    {
        return in_array($this->getValue(), WoundsOriginCodes::getTypeOfMechanicalWoundsCodes(), true);
    }

    /**
     * @return bool
     */
    public function isMechanicalStabWoundOrigin()
    {
        return $this->getValue() === WoundsOriginCodes::MECHANICAL_STAB;
    }

    /**
     * @return bool
     */
    public function isMechanicalCutWoundOrigin()
    {
        return $this->getValue() === WoundsOriginCodes::MECHANICAL_CUT;
    }

    /**
     * @return bool
     */
    public function isMechanicalCrushWoundOrigin()
    {
        return $this->getValue() === WoundsOriginCodes::MECHANICAL_CRUSH;
    }

    /**
     * @return bool
     */
    public function isElementalWoundOrigin()
    {
        return $this->getValue() === WoundsOriginCodes::ELEMENTAL;
    }

    /**
     * @return bool
     */
    public function isPsychicalWoundOrigin()
    {
        return $this->getValue() === WoundsOriginCodes::PSYCHICAL;
    }

    /**
     * @return bool
     */
    public function isSeriousWoundOrigin()
    {
        return in_array($this->getValue(), WoundsOriginCodes::getOriginWithTypeCodes(), true);
    }

    /**
     * @return bool
     */
    public function isOrdinaryWoundOrigin()
    {
        return !$this->isSeriousWoundOrigin();
    }

}