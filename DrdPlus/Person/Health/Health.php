<?php
namespace DrdPlus\Person\Health;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrineum\Entity\Entity;
use DrdPlus\Person\Health\Affliction\AfflictionByWound;
use DrdPlus\Properties\Derived\WoundsLimit;
use Granam\Strict\Object\StrictObject;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="health")
 */
class Health extends StrictObject implements Entity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @var ArrayCollection|Wound[]
     * @ORM\OneToMany(targetEntity="Wound", mappedBy="health", cascade={"all"}, orphanRemoval=true)
     */
    private $wounds;
    /**
     * @var GridOfWounds
     * @ORM\OneToOne(cascade={"all"}, fetch="EAGER", targetEntity="GridOfWounds", mappedBy="health")
     */
    private $gridOfWounds;
    /**
     * @var ArrayCollection|AfflictionByWound[]
     * @ORM\OneToMany(targetEntity="AfflictionByWound", mappedBy="health", cascade={"all"}, orphanRemoval=true)
     */
    private $afflictions;
    /**
     * @var int
     * @ORM\Column(type="smallint")
     */
    private $woundsLimitValue;

    public function __construct(WoundsLimit $woundsLimit)
    {
        $this->wounds = new ArrayCollection();
        $this->woundsLimitValue = $woundsLimit->getValue();
        $this->gridOfWounds = new GridOfWounds($this);
        $this->afflictions = new ArrayCollection();
    }

    /**
     * @param int $woundSize
     * @return Wound
     * @throws \DrdPlus\Person\Health\Exceptions\WoundHasToHaveSomeValue
     */
    public function createOrdinaryWound($woundSize)
    {
        $wound = new Wound($this, $woundSize, WoundOrigin::getOrdinaryWoundOrigin());
        $this->getWounds()->add($wound);
        $this->getGridOfWounds()->addPointsOfWound($wound->getPointsOfWound());

        return $wound;
    }

    /**
     * @param int $woundSize
     * @param WoundOrigin $woundOrigin
     * @param AfflictionByWound $afflictionByWound
     * @return Wound
     * @throws \DrdPlus\Person\Health\Exceptions\WoundHasToHaveSomeValue
     */
    public function createSeriousWound($woundSize, WoundOrigin $woundOrigin, AfflictionByWound $afflictionByWound)
    {
        $wound = new Wound($this, $woundSize, $woundOrigin);
        $this->getWounds()->add($wound);
        $this->getGridOfWounds()->addPointsOfWound($wound->getPointsOfWound());
        $this->getAfflictions()->add($afflictionByWound);

        return $wound;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ArrayCollection|Wound[]
     */
    public function getWounds()
    {
        return $this->wounds;
    }

    /**
     * @return GridOfWounds
     */
    public function getGridOfWounds()
    {
        $this->gridOfWounds;
    }

    /**
     * @return ArrayCollection|AfflictionByWound[]
     */
    public function getAfflictions()
    {
        return $this->afflictions;
    }

    /**
     * @return int
     */
    public function getWoundsLimitValue()
    {
        return $this->woundsLimitValue;
    }

    /**
     * @param WoundsLimit $woundsLimit
     */
    public function changeWoundsLimit(WoundsLimit $woundsLimit)
    {
        $this->woundsLimitValue = $woundsLimit->getValue();
    }

    /**
     * @return int
     */
    public function getNumberOfSeriousInjuries()
    {
        return count($this->getSeriousWounds());
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|Wound[]
     */
    public function getSeriousWounds()
    {
        return $this->getWounds()->filter(function (Wound $wound) {
            return $wound->isSerious();
        });
    }

    const DEADLY_NUMBER_OF_SERIOUS_INJURIES = 6;

    /**
     * @return bool
     */
    public function isAlive()
    {
        return
            $this->getGridOfWounds()->getRemainingHealth() > 0
            && $this->getNumberOfSeriousInjuries() < self::DEADLY_NUMBER_OF_SERIOUS_INJURIES;
    }

    /**
     * @return bool
     */
    public function isConscious()
    {
        return $this->getGridOfWounds()->getNumberOfFilledRows() >= 2;
    }

    /**
     * @return int
     */
    public function getMalusCausedByWounds()
    {
        // TODO
    }

}