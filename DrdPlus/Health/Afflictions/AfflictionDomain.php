<?php
namespace DrdPlus\Health\Afflictions;

use Doctrineum\String\StringEnum;
use DrdPlus\Codes\Body\AfflictionByWoundDomainCode;
use Granam\Tools\ValueDescriber;

/**
 * @method static AfflictionDomain getEnum($value)
 */
class AfflictionDomain extends StringEnum
{
    /**
     * @param string $domainCode
     * @return AfflictionDomain
     */
    public static function getIt($domainCode): AfflictionDomain
    {
        return static::getEnum($domainCode);
    }

    const PHYSICAL = AfflictionByWoundDomainCode::PHYSICAL;

    /**
     * @return AfflictionDomain
     */
    public static function getPhysicalDomain(): AfflictionDomain
    {
        return static::getEnum(self::PHYSICAL);
    }

    const PSYCHICAL = AfflictionByWoundDomainCode::PSYCHICAL;

    /**
     * @return AfflictionDomain
     */
    public static function getPsychicalDomain(): AfflictionDomain
    {
        return static::getEnum(self::PSYCHICAL);
    }

    protected static function convertToEnumFinalValue($enumValue): string
    {
        $finalValue = parent::convertToEnumFinalValue($enumValue);
        if (!in_array($finalValue, AfflictionByWoundDomainCode::getPossibleValues(), true)) {
            throw new Exceptions\UnknownAfflictionDomain('unexpected affliction domain ' . ValueDescriber::describe($enumValue));
        }

        return $finalValue;
    }

}