<?php
namespace DrdPlus\Health;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrineum\Entity\Entity;
use Doctrine\ORM\Mapping as ORM;
use DrdPlus\Codes\Body\OrdinaryWoundOriginCode;
use DrdPlus\Codes\Body\SeriousWoundOriginCode;
use DrdPlus\Codes\Body\WoundOriginCode;
use DrdPlus\Properties\Derived\Toughness;
use Granam\Integer\IntegerInterface;
use Granam\Strict\Object\StrictObject;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="severity", type="string")
 * @ORM\DiscriminatorMap({"ordinary" = "OrdinaryWound", "serious" = "SeriousWound"})
 */
abstract class Wound extends StrictObject implements Entity, IntegerInterface
{
    /**
     * @var int
     * @ORM\Id @ORM\GeneratedValue(strategy="AUTO") @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @var Health
     * @ORM\ManyToOne(targetEntity="Health", inversedBy="wounds")
     */
    private $health;
    /**
     * @var ArrayCollection|PointOfWound[]
     * @ORM\OneToMany(cascade={"all"}, targetEntity="PointOfWound", mappedBy="wound", orphanRemoval=true)
     */
    private $pointsOfWound;
    /**
     * @var WoundOriginCode
     * @ORM\Column(type="wound_origin_code")
     */
    private $woundOriginCode;
    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $old;

    /**
     * @param Health $health
     * @param WoundSize $woundSize (it can be also zero; usable for afflictions without a damage at all)
     * @param WoundOriginCode $woundOriginCode Ordinary origin is for lesser wound, others for serious wound
     * @throws \DrdPlus\Health\Exceptions\WoundHasToBeCreatedByHealthItself
     */
    protected function __construct(Health $health, WoundSize $woundSize, WoundOriginCode $woundOriginCode)
    {
        $this->checkIfCreatedByGivenHealth($health);
        $this->health = $health;
        $this->pointsOfWound = new ArrayCollection($this->createPointsOfWound($woundSize));
        $this->woundOriginCode = $woundOriginCode;
        $this->old = false;
    }

    /**
     * @param Health $health
     * @throws \DrdPlus\Health\Exceptions\WoundHasToBeCreatedByHealthItself
     */
    private function checkIfCreatedByGivenHealth(Health $health)
    {
        if (!$health->isOpenForNewWound()) {
            throw new Exceptions\WoundHasToBeCreatedByHealthItself(
                'Given health is not open for new wounds. Every wound has to be created by health itself.'
            );
        }
    }

    /**
     * @param WoundSize $woundSize
     * @return PointOfWound[]|array
     */
    private function createPointsOfWound(WoundSize $woundSize): array
    {
        $pointsOfWound = [];
        for ($wounded = $woundSize->getValue(); $wounded > 0; $wounded--) {
            $pointsOfWound[] = new PointOfWound($this); // implicit value of point of wound is 1
        }

        return $pointsOfWound;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHealth(): Health
    {
        return $this->health;
    }

    /**
     * @return Collection|PointOfWound[]
     */
    public function getPointsOfWound(): Collection
    {
        return clone $this->pointsOfWound; // to avoid external changes of the collection
    }

    /**
     * @return SeriousWoundOriginCode|OrdinaryWoundOriginCode|WoundOriginCode
     */
    public function getWoundOriginCode(): WoundOriginCode
    {
        return $this->woundOriginCode;
    }

    public function getValue(): int
    {
        // each point has value of 1, therefore count is enough
        return \count($this->getPointsOfWound());
    }

    public function getWoundSize(): WoundSize
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return WoundSize::createIt($this->getValue());
    }

    abstract public function isSerious(): bool;

    abstract public function isOrdinary(): bool;

    /**
     * @param HealingPower $healingPower
     * @param Toughness $toughness
     * @return int amount of healed points of wound
     */
    public function heal(HealingPower $healingPower, Toughness $toughness): int
    {
        $this->setOld(); // any wound is "old", treated and can be healed by regeneration or a true professional only
        // technical note: orphaned points of wound are removed automatically on persistence
        if ($healingPower->getHealUpTo($toughness) >= $this->getValue()) { // there is power to heal it all
            $healed = $this->getValue();
            $this->pointsOfWound->clear(); // unbinds all the points of wound

            return $healed;
        }
        $healed = 0;
        for ($healing = 1; $healing <= $healingPower->getHealUpTo($toughness); $healing++) {
            $this->pointsOfWound->removeElement($this->pointsOfWound->last());
            $healed++;
        }

        return $healed; // just a partial heal
    }

    /**
     * @return bool
     */
    public function isHealed(): bool
    {
        return $this->getValue() === 0;
    }

    /**
     * @return bool
     */
    public function isOld(): bool
    {
        return $this->old;
    }

    /**
     * @return bool
     */
    public function isFresh(): bool
    {
        return !$this->old;
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