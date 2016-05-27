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
use DrdPlus\Person\Health\Afflictions\Effects\CrackedBonesEffect;
use DrdPlus\Person\Health\Afflictions\ElementalPertinence\EarthPertinence;
use DrdPlus\Person\Health\SeriousWound;

class CrackedBones extends AfflictionByWound
{
    const CRACKED_BONES = 'cracked_bones';

    /**
     * @param SeriousWound $seriousWound
     * @return CrackedBones
     * @throws \DrdPlus\Person\Health\Afflictions\Exceptions\WoundHasToBeFreshForAffliction
     */
    public static function createIt(SeriousWound $seriousWound)
    {
        // see PPH page 78 right column, Cracked bones
        $sizeValue = $seriousWound->getHealth()->getGridOfWounds()->calculateFilledHalfRowsFor($seriousWound->getValue()) * 2;

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new static(
            $seriousWound,
            AfflictionDomain::getPhysicalAffliction(),
            AfflictionVirulence::getDayVirulence(),
            AfflictionSource::getPassiveSource(),
            AfflictionProperty::getIt(PropertyCodes::TOUGHNESS),
            AfflictionDangerousness::getIt(15),
            AfflictionSize::getIt($sizeValue),
            EarthPertinence::getMinus(),
            CrackedBonesEffect::getIt(),
            new \DateInterval('PT0S'), // immediately
            AfflictionName::getIt(self::CRACKED_BONES)
        );
    }
}