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
use DrdPlus\Person\Health\Afflictions\Exceptions\AfflictionSizeCanNotBeNegative;
use DrdPlus\Person\Health\Wound;

/**
 * See PPH page 78, right column
 */
class Bleeding extends AfflictionByWound
{
    const BLEEDING = 'bleeding';

    /**
     * @param Wound $wound
     * @return Bleeding
     * @throws \DrdPlus\Person\Health\Afflictions\SpecificAfflictions\Exceptions\BleedingCanNotExistsDueToTooLowWound
     */
    public static function createIt(Wound $wound)
    {
        // see PPH page 78 right column, Bleeding
        $bleedingSizeValue= $wound->getHealth()->getGridOfWounds()->calculateFilledHalfRowsFor($wound->getValue()) - 1;
        try {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $size = AfflictionSize::getIt($bleedingSizeValue);
        } catch (AfflictionSizeCanNotBeNegative $afflictionSizeCanNotBeNegative) {
            throw new Exceptions\BleedingCanNotExistsDueToTooLowWound(
                "Size of bleeding resulted into {$bleedingSizeValue}"
            );
        }

        return new static(
            $wound,
            AfflictionDomain::getPhysicalAffliction(),
            AfflictionVirulence::getRoundVirulence(),
            AfflictionSource::getActiveSource(),
            AfflictionProperty::getIt(PropertyCodes::TOUGHNESS),
            AfflictionDangerousness::getIt(15),
            $size,
            WaterPertinence::getMinus(),
            BleedingEffect::getIt(),
            new \DateInterval('PT0S'), // immediately
            AfflictionName::getIt(self::BLEEDING)
        );
    }
}