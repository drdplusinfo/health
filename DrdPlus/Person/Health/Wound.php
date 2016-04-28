<?php
namespace DrdPlus\Person\Health;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrineum\Entity\Entity;
use Granam\Integer\Tools\ToInteger;
use Doctrine\ORM\Mapping as ORM;
use Granam\Strict\Object\StrictObject;
use Granam\Tools\ValueDescriber;

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
     * @param Health $health
     * @param int $woundSize
     * @param WoundOrigin $woundOrigin
     * @throws \DrdPlus\Person\Health\Exceptions\WoundHasToHaveSomeValue
     */
    public function __construct(Health $health, $woundSize, WoundOrigin $woundOrigin)
    {
        $this->health = $health;
        $this->pointsOfWound = new ArrayCollection($this->createPointsOfWound($woundSize));
        $this->woundOrigin = $woundOrigin;
    }

    /**
     * @param int $woundValue
     * @return PointOfWound[]|array
     * @throws \DrdPlus\Person\Health\Exceptions\WoundHasToHaveSomeValue
     */
    private function createPointsOfWound($woundValue)
    {
        try {
            $woundValue = ToInteger::toInteger($woundValue);
        } catch (\Granam\Integer\Tools\Exceptions\Exception $conversionException) {
            throw new Exceptions\WoundHasToHaveSomeValue(
                'Expected positive integer as wound value, got ' . ValueDescriber::describe($woundValue),
                $conversionException->getCode(),
                $conversionException
            );
        }
        if ($woundValue <= 0) {
            throw new Exceptions\WoundHasToHaveSomeValue(
                'Expected at least 1 as wound value, got ' . ValueDescriber::describe($woundValue)
            );
        }
        $pointsOfWound = [];
        for (; $woundValue > 0; $woundValue--) {
            $pointsOfWound[] = new PointOfWound($this);
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
     * @return ArrayCollection|PointOfWound[]
     */
    public function getPointsOfWound()
    {
        return $this->pointsOfWound;
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
        return count($this->getPointsOfWound());
    }
}