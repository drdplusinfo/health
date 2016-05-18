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
use DrdPlus\Person\Health\Afflictions\Effects\SeveredArmEffect;
use DrdPlus\Person\Health\Afflictions\ElementalPertinence\EarthPertinence;
use DrdPlus\Person\Health\Wound;

/**
 * See PPH page 78, right column
 */
class SeveredArm extends AfflictionByWound
{
    const SEVERED_ARM = 'severed_arm';
    const COMPLETELY_SEVERED_ARM = 'completely_severed_arm';
    const COMPLETELY_SEVERED_ARM_SIZE_VALUE = 6;

    /**
     * @param Wound $wound
     * @param int $afflictionSizeValue
     * @return SeveredArm
     * @throws \DrdPlus\Person\Health\Afflictions\Exceptions\AfflictionSizeCanNotBeNegative
     * @throws \DrdPlus\Person\Health\Afflictions\SpecificAfflictions\Exceptions\SeveredArmAfflictionSizeExceeded
     * @throws \Doctrineum\Integer\Exceptions\UnexpectedValueToConvert
     */
    public static function createIt(Wound $wound, $afflictionSizeValue = self::COMPLETELY_SEVERED_ARM_SIZE_VALUE)
    {
        if ($afflictionSizeValue > self::COMPLETELY_SEVERED_ARM_SIZE_VALUE) {
            throw new Exceptions\SeveredArmAfflictionSizeExceeded(
                'Size of an affliction caused by severed arm can not be greater than ' . self::COMPLETELY_SEVERED_ARM_SIZE_VALUE
            );
        }

        return new static(
            $wound,
            AfflictionDomain::getPhysicalAffliction(),
            AfflictionVirulence::getDayVirulence(),
            AfflictionSource::getFullDeformationSource(),
            AfflictionProperty::getIt(PropertyCodes::TOUGHNESS), // irrelevant, full deformation can not be avoided
            AfflictionDangerousness::getIt(0), // irrelevant, full deformation can not be avoided
            $size = AfflictionSize::getIt($afflictionSizeValue), // completely severed arm has +6, partially related lower
            EarthPertinence::getMinus(),
            SeveredArmEffect::getIt(),
            new \DateInterval('PT0S'), // immediately
            AfflictionName::getIt(
                $size->getValue() === self::COMPLETELY_SEVERED_ARM_SIZE_VALUE
                    ? self::COMPLETELY_SEVERED_ARM
                    : self::SEVERED_ARM
            )
        );
    }
}