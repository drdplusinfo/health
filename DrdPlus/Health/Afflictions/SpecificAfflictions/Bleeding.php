<?php
namespace DrdPlus\Health\Afflictions\SpecificAfflictions;

use DrdPlus\Codes\PropertyCode;
use DrdPlus\Health\Afflictions\AfflictionByWound;
use DrdPlus\Health\Afflictions\AfflictionDangerousness;
use DrdPlus\Health\Afflictions\AfflictionDomain;
use DrdPlus\Health\Afflictions\AfflictionName;
use DrdPlus\Health\Afflictions\AfflictionProperty;
use DrdPlus\Health\Afflictions\AfflictionSize;
use DrdPlus\Health\Afflictions\AfflictionSource;
use DrdPlus\Health\Afflictions\AfflictionVirulence;
use DrdPlus\Health\Afflictions\Effects\BleedingEffect;
use DrdPlus\Health\Afflictions\ElementalPertinence\WaterPertinence;
use DrdPlus\Health\Afflictions\Exceptions\AfflictionSizeCanNotBeNegative;
use DrdPlus\Health\SeriousWound;
use Doctrine\ORM\Mapping as ORM;
use DrdPlus\Properties\Derived\WoundBoundary;

/**
 * See PPH page 78, right column
 * @ORM\Entity
 */
class Bleeding extends AfflictionByWound
{
    const BLEEDING = 'bleeding';

    /**
     * @param SeriousWound $seriousWound
     * @param WoundBoundary $woundBoundary
     * @return Bleeding
     * @throws \DrdPlus\Health\Afflictions\SpecificAfflictions\Exceptions\BleedingCanNotExistsDueToTooLowWound
     * @throws \DrdPlus\Health\Afflictions\Exceptions\WoundHasToBeFreshForAffliction
     */
    public static function createIt(SeriousWound $seriousWound, WoundBoundary $woundBoundary)
    {
        // see PPH page 78 right column, Bleeding
        $bleedingSizeValue = $seriousWound->getHealth()->getGridOfWounds()
                ->calculateFilledHalfRowsFor($seriousWound->getWoundSize(), $woundBoundary) - 1;
        try {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $size = AfflictionSize::getIt($bleedingSizeValue);
        } catch (AfflictionSizeCanNotBeNegative $afflictionSizeCanNotBeNegative) {
            throw new Exceptions\BleedingCanNotExistsDueToTooLowWound(
                "Size of bleeding resulted into {$bleedingSizeValue}"
            );
        }

        return new static(
            $seriousWound,
            AfflictionDomain::getPhysicalAffliction(),
            AfflictionVirulence::getRoundVirulence(),
            AfflictionSource::getActiveSource(),
            AfflictionProperty::getIt(PropertyCode::TOUGHNESS),
            AfflictionDangerousness::getIt(15),
            $size,
            WaterPertinence::getMinus(),
            BleedingEffect::getIt(),
            new \DateInterval('PT0S'), // immediately
            AfflictionName::getIt(self::BLEEDING)
        );
    }
}