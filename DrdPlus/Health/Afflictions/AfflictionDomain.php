<?php
namespace DrdPlus\Health\Afflictions;

use Doctrineum\String\StringEnum;
use DrdPlus\Codes\AfflictionByWoundDomainCode;
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

    const PHYSICAL = AfflictionByWoundDomainCode::PHYSICAL;

    /**
     * @return AfflictionDomain
     */
    public static function getPhysicalAffliction()
    {
        return static::getEnum(self::PHYSICAL);
    }

    const PSYCHICAL = AfflictionByWoundDomainCode::PSYCHICAL;

    /**
     * @return AfflictionDomain
     */
    public static function getPsychicalAffliction()
    {
        return static::getEnum(self::PSYCHICAL);
    }

    protected static function convertToEnumFinalValue($enumValue)
    {
        $finalValue = parent::convertToEnumFinalValue($enumValue);
        if (!in_array($finalValue, AfflictionByWoundDomainCode::getAfflictionByWoundDomainCodes(), true)) {
            throw new Exceptions\UnknownAfflictionDomain('unexpected affliction domain ' . ValueDescriber::describe($enumValue));
        }

        return $finalValue;
    }

}