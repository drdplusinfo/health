<?php
namespace DrdPlus\Person\Health;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrineum\Entity\Entity;
use DrdPlus\Person\Health\Afflictions\AfflictionByWound;
use Doctrine\ORM\Mapping as ORM;
use Granam\Strict\Object\StrictObject;

/**
 * @ORM\Entity
 * @ORM\Table(name="wound")
 */
class Wound extends StrictObject implements Entity
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
     * @var ArrayCollection|AfflictionByWound[]
     * @ORM\OneToMany(targetEntity="\DrdPlus\Person\Health\Afflictions\AfflictionByWound", mappedBy="wound", cascade={"persist"})
     */
    private $afflictions;

    /**
     * @param Health $health
     * @param WoundSize $woundSize (it can be also zero; usable for afflictions without a damage at all)
     * @param WoundOrigin $woundOrigin
     * @throws \DrdPlus\Person\Health\Exceptions\WoundValueHasToBeAtLeastZero
     */
    public function __construct(Health $health, WoundSize $woundSize, WoundOrigin $woundOrigin)
    {
        $this->health = $health;
        $this->pointsOfWound = new ArrayCollection($this->createPointsOfWound($woundSize));
        $this->woundOrigin = $woundOrigin;
        $this->afflictions = new ArrayCollection();
    }

    /**
     * @param WoundSize $woundSize
     * @return PointOfWound[]|array
     * @throws \DrdPlus\Person\Health\Exceptions\WoundValueHasToBeAtLeastZero
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
     * @return WoundOrigin
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
    public function isSerious()
    {
        return !$this->getWoundOrigin()->isOrdinaryWoundOrigin();
    }

    /**
     * @param int $upTo
     * @return int amount of healed points of wound
     */
    public function heal($upTo)
    {
        // technical note: orphaned points of wound are removed automatically on persistence
        if ($upTo >= $this->getValue()) { // there is power to heal it all
            $this->pointsOfWound->clear(); // unbinds all the points of wound

            return $upTo;
        }
        $healed = 0;
        for ($healing = 1; $healing <= $upTo; $healing++) {
            array_pop($this->pointsOfWound);
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
}