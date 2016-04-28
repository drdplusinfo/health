<?php
namespace DrdPlus\Person\Health;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrineum\Entity\Entity;
use Granam\Strict\Object\StrictObject;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="grid_of_wounds")
 */
class GridOfWounds extends StrictObject implements Entity
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
     * @ORM\OneToOne(cascade={"all"}, fetch="EAGER", targetEntity="Health", inversedBy="gridOfWounds")
     */
    private $health;

    /**
     * @var TreatmentBoundary
     * @ORM\Column(type="treatment_boundary")
     */
    private $treatmentBoundary;

    /**
     * @var PointOfWound[]|Collection
     * @ORM\OneToMany(targetEntity="PointOfWound", mappedBy="", cascade={strategy="persist"})
     */
    private $pointsOfWounds;

    /**
     * @param Health $health
     * @throws \DrdPlus\Person\Health\Exceptions\WoundsPerRowHasToBeGreaterThanZero
     */
    public function __construct(Health $health)
    {
        $this->health = $health;
        $this->treatmentBoundary = TreatmentBoundary::getIt(0);
        $this->pointsOfWounds = new ArrayCollection();
    }

    /**
     * @param Collection|PointOfWound[] $pointsOfWound
     */
    public function addPointsOfWound(Collection $pointsOfWound)
    {
        $pointsOfWound->map(function (PointOfWound $newPointOfWound) {
            $this->getPointsOfWounds()->add($newPointOfWound);
        });
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
     * @return int
     */
    public function getSumOfWounds()
    {
        return array_sum(
            array_map(
                function (PointOfWound $pointOfWound) {
                    return $pointOfWound->getValue();
                },
                $this->getPointsOfWounds()
            )
        );
    }

    /**
     * @return int
     */
    public function getMaximumWoundsPerRow()
    {
        return $this->getHealth()->getWoundsLimitValue();
    }

    /**
     * @return int
     */
    public function getRemainingHealth()
    {
        return max($this->getMaximumHealth() - $this->getSumOfWounds(), 0);
    }

    const NUMBER_OF_ROWS = 3;

    /**
     * @return int
     */
    public function getMaximumHealth()
    {
        return $this->health * self::NUMBER_OF_ROWS;
    }

    /**
     * @return Collection|PointOfWound[]
     */
    public function getPointsOfWounds()
    {
        return $this->pointsOfWounds;
    }

    /**
     * Treatment boundary is set automatically on any heal (lowering wounds) or new serious injury
     * @return TreatmentBoundary
     */
    public function getTreatmentBoundary()
    {
        return $this->treatmentBoundary;
    }

    /**
     * @return int
     */
    public function getNumberOfFilledRows()
    {
        return (int)floor($this->getSumOfWounds() / $this->getMaximumWoundsPerRow());
    }

}