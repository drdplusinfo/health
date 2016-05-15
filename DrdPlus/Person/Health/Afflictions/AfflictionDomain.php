<?php
namespace DrdPlus\Person\Health\Afflictions;

use Doctrineum\String\StringEnum;
use DrdPlus\Codes\AfflictionByWoundDomainCodes;
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
        return static::getEnum(AfflictionByWoundDomainCodes::PHYSICAL);
    }

    /**
     * @return AfflictionDomain
     */
    public static function getPsychicalAffliction()
    {
        return static::getEnum(AfflictionByWoundDomainCodes::PSYCHICAL);
    }

    protected static function convertToEnumFinalValue($enumValue)
    {
        $finalValue = parent::convertToEnumFinalValue($enumValue);
        if (!in_array($finalValue, AfflictionByWoundDomainCodes::getAfflictionDomainCodes(), true)) {
            throw new Exceptions\UnknownAfflictionDomain('unexpected affliction domain ' . ValueDescriber::describe($enumValue));
        }

        return $finalValue;
    }

}