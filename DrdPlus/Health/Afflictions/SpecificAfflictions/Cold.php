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
use DrdPlus\Health\Afflictions\Effects\ColdEffect;
use DrdPlus\Health\Afflictions\ElementalPertinence\WaterPertinence;
use DrdPlus\Health\SeriousWound;
use Doctrine\ORM\Mapping as ORM;

/**
 * See PPH page 78, left column
 *
 * @ORM\Entity
 * @method ColdEffect getAfflictionEffect()
 */
class Cold extends AfflictionByWound
{
    const COLD = 'cold';

    /**
     * @param SeriousWound $seriousWound
     * @return Cold
     * @throws \DrdPlus\Health\Afflictions\Exceptions\WoundHasToBeFreshForAffliction
     */
    public static function createIt(SeriousWound $seriousWound)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new static(
            $seriousWound,
            AfflictionProperty::getIt(PropertyCode::TOUGHNESS),
            AfflictionDangerousness::getIt(7),
            AfflictionDomain::getPhysicalDomain(),
            AfflictionVirulence::getDayVirulence(),
            AfflictionSource::getActiveSource(),
            AfflictionSize::getIt(4),
            WaterPertinence::getPlus(),
            ColdEffect::getIt(),
            new \DateInterval('P1D'),
            AfflictionName::getIt(self::COLD)
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
        return $this->getAfflictionEffect()->getAgilityMalus($this);
    }

    /**
     * @return int
     */
    public function getKnackMalus()
    {
        return $this->getAfflictionEffect()->getKnackMalus($this);
    }

}