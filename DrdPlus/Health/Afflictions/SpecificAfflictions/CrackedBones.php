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
 * @method CrackedBonesEffect getAfflictionEffect()
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
        $sizeValue = 2 * $seriousWound
                ->getHealth()
                ->getGridOfWounds()
                ->calculateFilledHalfRowsFor($seriousWound->getWoundSize(), $woundBoundary);

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new static(
            $seriousWound,
            AfflictionProperty::getIt(PropertyCode::TOUGHNESS),
            AfflictionDangerousness::getIt(15),
            AfflictionDomain::getPhysicalDomain(),
            AfflictionVirulence::getDayVirulence(),
            AfflictionSource::getPassiveSource(),
            AfflictionSize::getIt($sizeValue),
            EarthPertinence::getMinus(),
            CrackedBonesEffect::getIt(),
            new \DateInterval('PT0S'), // immediately
            AfflictionName::getIt(self::CRACKED_BONES)
        );
    }

    /**
     * @return int
     */
    public function getHealMalus()
    {
        return $this->getAfflictionEffect()->getHealingMalus($this);
    }

    /**
     * @return int
     */
    public function getMalusToActivities()
    {
        return 0;
    }

    /**
     * @return int
     */
    public function getStrengthMalus()
    {
        return 0;
    }

    /**
     * @return int
     */
    public function getAgilityMalus()
    {
        return 0;
    }

    /**
     * @return int
     */
    public function getKnackMalus()
    {
        return 0;
    }
}