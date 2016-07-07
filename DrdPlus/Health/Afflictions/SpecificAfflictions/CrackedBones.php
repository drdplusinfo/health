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
use DrdPlus\Health\Afflictions\Effects\CrackedBonesEffect;
use DrdPlus\Health\Afflictions\ElementalPertinence\EarthPertinence;
use DrdPlus\Health\SeriousWound;
use Doctrine\ORM\Mapping as ORM;
use DrdPlus\Properties\Derived\WoundBoundary;

/**
 * @ORM\Entity
 */
class CrackedBones extends AfflictionByWound
{
    const CRACKED_BONES = 'cracked_bones';

    /**
     * @param SeriousWound $seriousWound
     * @param WoundBoundary $woundBoundary
     * @return CrackedBones
     * @throws \DrdPlus\Health\Afflictions\Exceptions\WoundHasToBeFreshForAffliction
     */
    public static function createIt(SeriousWound $seriousWound, WoundBoundary $woundBoundary)
    {
        // see PPH page 78 right column, Cracked bones
        $sizeValue = $seriousWound->getHealth()->getGridOfWounds()
                ->calculateFilledHalfRowsFor($seriousWound->getWoundSize(), $woundBoundary) * 2;

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new static(
            $seriousWound,
            AfflictionDomain::getPhysicalAffliction(),
            AfflictionVirulence::getDayVirulence(),
            AfflictionSource::getPassiveSource(),
            AfflictionProperty::getIt(PropertyCode::TOUGHNESS),
            AfflictionDangerousness::getIt(15),
            AfflictionSize::getIt($sizeValue),
            EarthPertinence::getMinus(),
            CrackedBonesEffect::getIt(),
            new \DateInterval('PT0S'), // immediately
            AfflictionName::getIt(self::CRACKED_BONES)
        );
    }
}