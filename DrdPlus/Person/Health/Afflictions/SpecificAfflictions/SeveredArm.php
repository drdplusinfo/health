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
use DrdPlus\Person\Health\SeriousWound;
use Doctrine\ORM\Mapping as ORM;

/**
 * See PPH page 78, right column
 *
 * @ORM\Entity
 */
class SeveredArm extends AfflictionByWound
{
    const SEVERED_ARM = 'severed_arm';
    const COMPLETELY_SEVERED_ARM = 'completely_severed_arm';
    const COMPLETELY_SEVERED_ARM_SIZE = 6;

    /**
     * @param SeriousWound $seriousWound
     * @param int $sizeValue
     * @return SeveredArm
     * @throws \DrdPlus\Person\Health\Afflictions\Exceptions\AfflictionSizeCanNotBeNegative
     * @throws \DrdPlus\Person\Health\Afflictions\SpecificAfflictions\Exceptions\SeveredArmAfflictionSizeExceeded
     * @throws \Doctrineum\Integer\Exceptions\UnexpectedValueToConvert
     * @throws \DrdPlus\Person\Health\Afflictions\Exceptions\WoundHasToBeFreshForAffliction
     */
    public static function createIt(SeriousWound $seriousWound, $sizeValue = self::COMPLETELY_SEVERED_ARM_SIZE)
    {
        $size = AfflictionSize::getIt($sizeValue); // completely severed arm has +6, partially related lower
        if ($size->getValue() > self::COMPLETELY_SEVERED_ARM_SIZE) {
            throw new Exceptions\SeveredArmAfflictionSizeExceeded(
                'Size of an affliction caused by severed arm can not be greater than ' . self::COMPLETELY_SEVERED_ARM_SIZE
            );
        }

        return new static(
            $seriousWound,
            AfflictionDomain::getPhysicalAffliction(),
            AfflictionVirulence::getDayVirulence(),
            AfflictionSource::getFullDeformationSource(),
            AfflictionProperty::getIt(PropertyCodes::TOUGHNESS), // irrelevant, full deformation can not be avoided
            AfflictionDangerousness::getIt(0), // irrelevant, full deformation can not be avoided
            $size,
            EarthPertinence::getMinus(),
            SeveredArmEffect::getIt(),
            new \DateInterval('PT0S'), // immediately
            AfflictionName::getIt(
                $size->getValue() === self::COMPLETELY_SEVERED_ARM_SIZE
                    ? self::COMPLETELY_SEVERED_ARM
                    : self::SEVERED_ARM
            )
        );
    }
}