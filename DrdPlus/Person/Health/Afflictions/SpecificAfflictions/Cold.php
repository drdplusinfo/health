<?php
namespace DrdPlus\Person\Health\Afflictions\SpecificAfflictions;

use DrdPlus\Codes\PropertyCodes;
use DrdPlus\Person\Health\Afflictions\AfflictionByWound;
use DrdPlus\Person\Health\Afflictions\AfflictionDangerousness;
use DrdPlus\Person\Health\Afflictions\AfflictionDomain;
use DrdPlus\Person\Health\Afflictions\AfflictionProperty;
use DrdPlus\Person\Health\Afflictions\AfflictionSize;
use DrdPlus\Person\Health\Afflictions\AfflictionSource;
use DrdPlus\Person\Health\Afflictions\AfflictionVirulence;
use DrdPlus\Person\Health\Afflictions\Effects\ColdEffect;
use DrdPlus\Person\Health\Afflictions\ElementalPertinence\WaterPertinence;
use DrdPlus\Person\Health\Wound;

/**
 * See PPH page 78, left column
 */
class Cold extends AfflictionByWound
{
    const COLD = 'cold';

    /**
     * @param Wound $wound
     * @return Cold
     */
    public static function createIt(Wound $wound)
    {
        return new static(
            $wound,
            AfflictionDomain::getPhysicalAffliction(),
            AfflictionVirulence::getDayVirulence(),
            AfflictionSource::getActiveSource(),
            AfflictionProperty::getIt(PropertyCodes::TOUGHNESS),
            AfflictionDangerousness::getIt(7),
            AfflictionSize::getIt(4),
            WaterPertinence::getPlus(),
            ColdEffect::getIt(),
            new \DateInterval('P1D'),
            self::COLD // name
        );
    }
}