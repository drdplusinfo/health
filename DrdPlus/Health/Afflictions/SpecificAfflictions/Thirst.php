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
    public static function createIt(Health $health, AfflictionSize $daysOfThirst): Thirst
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
    public function getHealMalus(): int
    {
        return 0;
    }

    /**
     * @return int
     */
    public function getMalusToActivities(): int
    {
        return 0;
    }

    /**
     * @return int
     */
    public function getStrengthMalus(): int
    {
        return -$this->getAfflictionSize()->getValue();
    }

    /**
     * @return int
     */
    public function getAgilityMalus(): int
    {
        return -$this->getAfflictionSize()->getValue();
    }

    /**
     * @return int
     */
    public function getKnackMalus(): int
    {
        return -$this->getAfflictionSize()->getValue();
    }

    /**
     * @return int
     */
    public function getWillMalus(): int
    {
        return -$this->getAfflictionSize()->getValue();
    }

    /**
     * @return int
     */
    public function getIntelligenceMalus(): int
    {
        return -$this->getAfflictionSize()->getValue();
    }

    /**
     * @return int
     */
    public function getCharismaMalus(): int
    {
        return -$this->getAfflictionSize()->getValue();
    }

}