<?php
namespace DrdPlus\Person\Health\Afflictions\SpecificAfflictions;

use DrdPlus\Codes\PropertyCodes;
use DrdPlus\Person\Health\Afflictions\AfflictionByWound;
use DrdPlus\Person\Health\Afflictions\AfflictionDangerousness;
use DrdPlus\Person\Health\Afflictions\AfflictionDomain;
use DrdPlus\Person\Health\Afflictions\AfflictionName;
use DrdPlus\Person\Health\Afflictions\AfflictionProperty;
use DrdPlus\Person\Health\Afflictions\AfflictionSize;
use DrdPlus\Person\Health\Afflictions\AfflictionSource;
use DrdPlus\Person\Health\Afflictions\AfflictionVirulence;
use DrdPlus\Person\Health\Afflictions\Effects\BleedingEffect;
use DrdPlus\Person\Health\Afflictions\ElementalPertinence\WaterPertinence;
use DrdPlus\Person\Health\Wound;

/**
 * See PPH page 78, right column
 */
class Bleeding extends AfflictionByWound
{
    const BLEEDING = 'bleeding';

    /**
     * @param Wound $wound
     * @return Cold
     */
    public static function createIt(Wound $wound)
    {
        // see PPH page 78 right column, Bleeding
        $sizeValue = $wound->getHealth()->getGridOfWounds()->calculateFilledHalfRowsFor($wound->getValue()) - 1;

        return new static(
            $wound,
            AfflictionDomain::getPhysicalAffliction(),
            AfflictionVirulence::getRoundVirulence(),
            AfflictionSource::getActiveSource(),
            AfflictionProperty::getIt(PropertyCodes::TOUGHNESS),
            AfflictionDangerousness::getIt(15),
            AfflictionSize::getIt($sizeValue),
            WaterPertinence::getMinus(),
            BleedingEffect::getIt(),
            new \DateInterval('PT0S'), // immediately
            AfflictionName::getIt(self::BLEEDING)
        );
    }
}