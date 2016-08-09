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
use DrdPlus\Health\Afflictions\Effects\SeveredArmEffect;
use DrdPlus\Health\Afflictions\ElementalPertinence\EarthPertinence;
use DrdPlus\Health\SeriousWound;
use Doctrine\ORM\Mapping as ORM;

/**
 * See PPH page 78, right column
 *
 * @ORM\Entity
 * @method SeveredArmEffect getAfflictionEffect()
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
     * @throws \DrdPlus\Health\Afflictions\Exceptions\AfflictionSizeCanNotBeNegative
     * @throws \DrdPlus\Health\Afflictions\SpecificAfflictions\Exceptions\SeveredArmAfflictionSizeExceeded
     * @throws \Doctrineum\Integer\Exceptions\UnexpectedValueToConvert
     * @throws \DrdPlus\Health\Afflictions\Exceptions\WoundHasToBeFreshForAffliction
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
            AfflictionProperty::getIt(PropertyCode::TOUGHNESS), // irrelevant, full deformation can not be avoided
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

    /**
     * @return int
     */
    public function getHealMalus()
    {
        return 0;
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
        return $this->getAfflictionEffect()->getStrengthMalus($this);
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
        return $this->getAfflictionEffect()->getKnackMalus($this);
    }
}