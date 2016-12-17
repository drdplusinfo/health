<?php
namespace DrdPlus\Health\Afflictions\SpecificAfflictions;

use Doctrine\ORM\Mapping as ORM;
use DrdPlus\Health\Afflictions\Affliction;
use DrdPlus\Health\Afflictions\AfflictionDangerousness;
use DrdPlus\Health\Afflictions\AfflictionDomain;
use DrdPlus\Health\Afflictions\AfflictionName;
use DrdPlus\Health\Afflictions\AfflictionProperty;
use DrdPlus\Health\Afflictions\AfflictionSize;
use DrdPlus\Health\Afflictions\AfflictionSource;
use DrdPlus\Health\Afflictions\AfflictionVirulence;
use DrdPlus\Health\Afflictions\Effects\ThirstEffect;
use DrdPlus\Health\Afflictions\ElementalPertinence\WaterPertinence;
use DrdPlus\Health\Health;

/**
 * @ORM\Entity
 */
class Thirst extends Affliction
{

    /**
     * @param Health $health
     * @param AfflictionSize $daysOfThirst
     * @return Thirst
     * @throws \DrdPlus\Health\Exceptions\UnknownAfflictionOriginatingWound
     * @throws \DrdPlus\Health\Exceptions\AfflictionIsAlreadyRegistered
     */
    public static function createIt(Health $health, AfflictionSize $daysOfThirst)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new static(
            $health,
            AfflictionProperty::getIt(AfflictionProperty::ENDURANCE), // irrelevant, thirst can not be avoided
            AfflictionDangerousness::getIt(9999), // irrelevant, thirst can not be avoided
            AfflictionDomain::getPhysicalDomain(),
            AfflictionVirulence::getDayVirulence(),
            AfflictionSource::getPassiveSource(),
            $daysOfThirst,
            WaterPertinence::getMinus(),
            ThirstEffect::getIt(),
            new \DateInterval('P1D'),
            AfflictionName::getIt('thirst')
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
        return -$this->getSize()->getValue();
    }

    /**
     * @return int
     */
    public function getAgilityMalus()
    {
        return -$this->getSize()->getValue();
    }

    /**
     * @return int
     */
    public function getKnackMalus()
    {
        return -$this->getSize()->getValue();
    }

    /**
     * @return int
     */
    public function getWillMalus()
    {
        return -$this->getSize()->getValue();
    }

    /**
     * @return int
     */
    public function getIntelligenceMalus()
    {
        return -$this->getSize()->getValue();
    }

    /**
     * @return int
     */
    public function getCharismaMalus()
    {
        return -$this->getSize()->getValue();
    }

}