<?php
namespace DrdPlus\Person\Health;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrineum\Entity\Entity;
use Doctrine\ORM\Mapping as ORM;
use Granam\Strict\Object\StrictObject;

/**
 * @ORM\MappedSuperclass()
 */
abstract class Wound extends StrictObject implements Entity
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Health
     * @ORM\ManyToOne(targetEntity="Health", inversedBy="wounds")
     */
    private $health;

    /**
     * @var ArrayCollection|PointOfWound[]
     * @ORM\OneToMany(cascade={"all"}, targetEntity="PointOfWound", orphanRemoval=true)
     */
    private $pointsOfWound;

    /**
     * @var WoundOrigin
     * @ORM\Column(type="wound_origin")
     */
    private $woundOrigin;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $old;

    /**
     * @param Health $health
     * @param WoundSize $woundSize (it can be also zero; usable for afflictions without a damage at all)
     * @param WoundOrigin $woundOrigin Ordinary origin is for lesser wound, others for serious wound
     */
    protected function __construct(Health $health, WoundSize $woundSize, WoundOrigin $woundOrigin)
    {
        $this->health = $health;
        $this->pointsOfWound = new ArrayCollection($this->createPointsOfWound($woundSize));
        $this->woundOrigin = $woundOrigin;
        $this->old = false;
    }

    /**
     * @param WoundSize $woundSize
     * @return PointOfWound[]|array
     */
    private function createPointsOfWound(WoundSize $woundSize)
    {
        $pointsOfWound = [];
        for ($wounded = $woundSize->getValue(); $wounded > 0; $wounded--) {
            $pointsOfWound[] = new PointOfWound($this); // implicit value of point of wound is 1
        }

        return $pointsOfWound;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Health
     */
    public function getHealth()
    {
        return $this->health;
    }

    /**
     * @return array|PointOfWound[]
     */
    public function getPointsOfWound()
    {
        return $this->pointsOfWound->toArray(); // to avoid external changes of the collection
    }

    /**
     * @return SpecificWoundOrigin|OrdinaryWoundOrigin
     */
    public function getWoundOrigin()
    {
        return $this->woundOrigin;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        // each point has value of 1, therefore count is enough
        return count($this->getPointsOfWound());
    }

    /**
     * @return bool
     */
    abstract public function isSerious();

    /**
     * @param HealingPower $healingPower
     * @return int amount of healed points of wound
     */
    public function heal(HealingPower $healingPower)
    {
        $this->setOld(); // any wound is "old", treated and can be healed by regeneration or professional only
        // technical note: orphaned points of wound are removed automatically on persistence
        if ($healingPower->getHealUpTo() >= $this->getValue()) { // there is power to heal it all
            $healed = $this->getValue();
            $this->pointsOfWound->clear(); // unbinds all the points of wound

            return $healed;
        }
        $healed = 0;
        for ($healing = 1; $healing <= $healingPower->getHealUpTo(); $healing++) {
            $this->pointsOfWound->removeElement($this->pointsOfWound->last());
            $healed++;
        }

        return $healed; // just a partial heal
    }

    /**
     * @return bool
     */
    public function isHealed()
    {
        return $this->getValue() === 0;
    }

    /**
     * @return bool
     */
    public function isOld()
    {
        return $this->old;
    }

    public function setOld()
    {
        $this->old = true;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getValue();
    }
}