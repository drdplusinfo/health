<?php
namespace DrdPlus\Person\Health\Afflictions;

use Doctrineum\String\StringEnum;
use DrdPlus\Codes\AfflictionByWoundCodes;
use Granam\Tools\ValueDescriber;

class AfflictionDomain extends StringEnum
{
    /**
     * @param string $domainCode
     * @return AfflictionDomain
     */
    public static function getIt($domainCode)
    {
        return static::getEnum($domainCode);
    }
    /**
     * @return AfflictionDomain
     */
    public static function getPhysicalAffliction()
    {
        return static::getEnum(AfflictionByWoundCodes::PHYSICAL);
    }

    /**
     * @return AfflictionDomain
     */
    public static function getPsychicalAffliction()
    {
        return static::getEnum(AfflictionByWoundCodes::PSYCHICAL);
    }

    protected static function convertToEnumFinalValue($enumValue)
    {
        $finalValue = parent::convertToEnumFinalValue($enumValue);
        if (!in_array($finalValue, AfflictionByWoundCodes::getAfflictionDomainCodes(), true)) {
            throw new Exceptions\UnknownAfflictionDomain('unexpected affliction domain ' . ValueDescriber::describe($enumValue));
        }

        return $finalValue;
    }

}