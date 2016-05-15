<?php
namespace DrdPlus\Person\Health;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrineum\Entity\Entity;
use Drd\DiceRoll\Templates\Rollers\Roller2d6DrdPlus;
use DrdPlus\Person\Health\Afflictions\AfflictionByWound;
use DrdPlus\Person\Health\Afflictions\SpecificAfflictions\Pain;
use DrdPlus\Properties\Base\Will;
use DrdPlus\Properties\Derived\WoundsLimit;
use DrdPlus\RollsOn\Traps\RollOnWillAgainstMalus;
use DrdPlus\RollsOn\Traps\RollOnWill;
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
     * @var ArrayCollection|AfflictionByWound[]
     * @ORM\OneToMany(targetEntity="AfflictionByWound", mappedBy="health", cascade={"all"}, orphanRemoval=true)
     */
    private $afflictions;
    /**
     * @var int
     * @ORM\Column(type="smallint")
     */
    private $woundsLimitValue;
    /**
     * Separates new and old (or serious) injuries.
     * @var TreatmentBoundary
     * @ORM\Column(type="treatment_boundary")
     */
    private $treatmentBoundary;
    /**
     * @var GridOfWounds is just a helper, does not need to be persisted
     */
    private $gridOfWounds;

    public function __construct(WoundsLimit $woundsLimit)
    {
        $this->wounds = new ArrayCollection();
        $this->woundsLimitValue = $woundsLimit->getValue();
        $this->afflictions = new ArrayCollection();
        $this->treatmentBoundary = TreatmentBoundary::getIt(0);
    }

    /**
     * @param WoundSize $woundSize
     * @param Will $will
     * @param Roller2d6DrdPlus $roller2d6DrdPlus
     * @return Wound
     * @throws \DrdPlus\Person\Health\Exceptions\WoundSizeCanNotBeNegative
     */
    public function createOrdinaryWound(WoundSize $woundSize, Will $will, Roller2d6DrdPlus $roller2d6DrdPlus)
    {
        $wound = new Wound($this, $woundSize, WoundOrigin::getOrdinaryWoundOrigin());
        $this->wounds->add($wound);
        if ($this->maySufferFromPain()) {
            $this->reRollAgainstMalusFromWoundsOnWound($will, $roller2d6DrdPlus);
        }

        return $wound;
    }

    private function maySufferFromPain()
    {
        return
            $this->getGridOfWounds()->getNumberOfFilledRows() >= GridOfWounds::PAIN_NUMBER_OF_ROWS
            && $this->isConscious();
    }

    /**
     * @param WoundSize $woundSize
     * @param WoundOrigin $woundOrigin
     * @param AfflictionByWound $afflictionByWound
     * @param Will $will
     * @param Roller2d6DrdPlus $roller2d6DrdPlus
     * @return Wound
     * @throws \DrdPlus\Person\Health\Exceptions\WoundSizeCanNotBeNegative
     */
    public function createSeriousWound(
        WoundSize $woundSize,
        WoundOrigin $woundOrigin,
        AfflictionByWound $afflictionByWound,
        Will $will,
        Roller2d6DrdPlus $roller2d6DrdPlus
    )
    {
        $wound = new Wound($this, $woundSize, $woundOrigin);
        $this->wounds->add($wound);
        $this->getAfflictions()->add($afflictionByWound);
        if ($this->maySufferFromPain()) {
            $this->reRollAgainstMalusFromWoundsOnWound($will, $roller2d6DrdPlus);
        }
        $this->treatmentBoundary = TreatmentBoundary::getIt(
            $this->getTreatmentBoundary()->getValue() + $wound->getValue()
        );

        return $wound;
    }

    /**
     * Also sets treatment boundary to unhealed wounds after. Even if the heal itself heals nothing!
     * @param int $healUpTo
     * @return int amount of actually healed points of wounds
     */
    public function healOrdinaryWoundsUpTo($healUpTo)
    {
        // can heal new and ordinary wounds only, up to limit by current treatment boundary
        $healed = 0;
        foreach ($this->getUnhealedOrdinaryWounds() as $unhealedOrdinaryWound) {
            $woundValueBeforeHeal = $unhealedOrdinaryWound->getValue();
            $unhealedOrdinaryWound->heal($healUpTo - $healed);
            // difference of previous wounds and current is the healed amount
            $healed += ($woundValueBeforeHeal - $unhealedOrdinaryWound->getValue());
            if ($healUpTo === $healed) {
                break; // we spent all the healing power
            }
        }
        // all unhealed wounds become "old" (and can be healed only by a professional or nature itself)
        $this->treatmentBoundary = TreatmentBoundary::getIt($this->getGridOfWounds()->getSumOfWounds());

        return $healed;
    }

    /**
     * @return Wound[]|Collection
     */
    private function getUnhealedOrdinaryWounds()
    {
        return $this->getUnhealedWounds()->filter(
            function (Wound $wound) {
                return !$wound->isSerious();
            }
        );
    }

    /**
     * Usable for info about amount of wounds which can be healed by basic healing
     * @return int
     */
    public function getUnhealedOrdinaryWoundsValue()
    {
        return array_sum(
            array_map(
                function (Wound $wound) {
                    return $wound->getValue();
                },
                $this->getUnhealedOrdinaryWounds()
            )
        );
    }

    /**
     * @param int $healUpTo
     * @return int amount of healed points of wounds
     */
    public function healSeriousAndOrdinaryWoundsUpTo($healUpTo)
    {
        // professional heal takes treatment boundary altogether
        $healed = 0;
        foreach ($this->getUnhealedWounds() as $unhealedWound) {
            $woundValueBeforeHeal = $unhealedWound->getValue();
            $unhealedWound->heal($healUpTo - $healed);
            // difference of previous wounds and current is the healed amount
            $healed += ($woundValueBeforeHeal - $unhealedWound->getValue());
            if ($healUpTo === $healed) {
                break; // we spent all the healing power
            }
        }
        // treatment boundary is taken with wounds down together
        $this->treatmentBoundary = TreatmentBoundary::getIt(
            $this->treatmentBoundary->getValue() - $healed
        );

        return $healed;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Collection|Wound[]
     */
    public function getUnhealedWounds()
    {
        return $this->wounds->filter( // results into different instance which avoids external change of the original
            function (Wound $wound) {
                return !$wound->isHealed();
            }
        );
    }

    /**
     * @return GridOfWounds
     */
    public function getGridOfWounds()
    {
        if ($this->gridOfWounds === null) {
            $this->gridOfWounds = new GridOfWounds($this);
        }

        return $this->gridOfWounds;
    }

    /**
     * @return Collection|AfflictionByWound[]
     */
    public function getAfflictions()
    {
        return clone $this->afflictions; // cloned to avoid external change of the collection
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
     * @param Will $will
     * @param Roller2d6DrdPlus $roller2d6DrdPlus
     */
    public function changeWoundsLimit(WoundsLimit $woundsLimit, Will $will, Roller2d6DrdPlus $roller2d6DrdPlus)
    {
        if ($this->getWoundsLimitValue() === $woundsLimit->getValue()) {
            return;
        }
        $previousHealthMaximum = $this->getGridOfWounds()->getHealthMaximum();
        $this->woundsLimitValue = $woundsLimit->getValue();
        if ($previousHealthMaximum > $this->gridOfWounds->getHealthMaximum()) { // current wounds relatively increases
            $this->reRollAgainstMalusFromWoundsOnWound($will, $roller2d6DrdPlus);
        } elseif ($previousHealthMaximum < $this->gridOfWounds->getHealthMaximum()) { // current wounds relatively decreases
            $this->reRollAgainstMalusFromWoundsOnHeal($will, $roller2d6DrdPlus);
        }
        $this->rollAgainstMalusFromWounds = null; // health changed, new conditions and therefore new roll on malus
    }

    private function reRollAgainstMalusFromWoundsOnHeal(Will $will, Roller2d6DrdPlus $roller2d6DrdPlus)
    {
        if ($this->rollAgainstMalusFromWounds === null) {
            return;
        }
        $newRoll = $this->createRollAgainstMalusFromWounds($will, $roller2d6DrdPlus);
        // lower (or same of course) malus remains; can not be increased on healing
        if ($this->rollAgainstMalusFromWounds->getMalusValue() <= $newRoll->getMalusValue()) {
            return;
        }
        $this->rollAgainstMalusFromWounds = $newRoll;
    }

    private function reRollAgainstMalusFromWoundsOnWound(Will $will, Roller2d6DrdPlus $roller2d6DrdPlus)
    {
        if ($this->rollAgainstMalusFromWounds === null) {
            return;
        }
        $newRoll = $this->createRollAgainstMalusFromWounds($will, $roller2d6DrdPlus);
        // greater (or same of course) malus remains; can not be decreased on new wounds
        if ($this->rollAgainstMalusFromWounds->getMalusValue() >= $newRoll->getMalusValue()) {
            return;
        }
        $this->rollAgainstMalusFromWounds = $newRoll;
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
    public function getNumberOfSeriousInjuries()
    {
        return $this->getUnhealedWounds()
            ->filter(
                function (Wound $wound) {
                    return $wound->isSerious();
                }
            )->count();
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
        return $this->getGridOfWounds()->getNumberOfFilledRows() < GridOfWounds::UNCONSCIOUS_NUMBER_OF_ROWS;
    }

    /**
     *
     * @param Will $will
     * @param Roller2d6DrdPlus $roller2d6DrdPlus
     * @return int
     */
    public function getSignificantMalus(Will $will, Roller2d6DrdPlus $roller2d6DrdPlus)
    {
        $maluses = [$this->getMalusCausedByWounds($will, $roller2d6DrdPlus)];
        foreach ($this->getPains() as $pain) {
            // for Pain see PPH page 79, left column
            $maluses[] = $pain->getEffect()->getMalusSize($pain);
        }

        return max($maluses);
    }

    /**
     * @param Will $will
     * @param Roller2d6DrdPlus $roller2d6DrdPlus
     * @return int
     */
    public function getMalusCausedByWounds(Will $will, Roller2d6DrdPlus $roller2d6DrdPlus)
    {
        if ($this->getGridOfWounds()->getNumberOfFilledRows() === 0) {
            return 0;
        }

        /**
         * note: Can grow only on another wound when on second row in grid of wounds.
         * Can decrease only on heal of any wound when on second row in grid of wounds.
         * Is removed (to get new roll) when first row of grid of wounds is not fully filled.
         * See PPH page 75 right column
         */
        return $this->getRollAgainstMalusFromWounds($will, $roller2d6DrdPlus)->getMalusValue();
    }

    /**
     * @var RollOnWillAgainstMalus|null
     */
    private $rollAgainstMalusFromWounds;

    /**
     * @param Will $will
     * @param Roller2d6DrdPlus $roller2d6DrdPlus
     * @return RollOnWillAgainstMalus
     */
    private function getRollAgainstMalusFromWounds(Will $will, Roller2d6DrdPlus $roller2d6DrdPlus)
    {
        if ($this->rollAgainstMalusFromWounds === null) {
            $this->rollAgainstMalusFromWounds = $this->createRollAgainstMalusFromWounds($will, $roller2d6DrdPlus);
        }

        return $this->rollAgainstMalusFromWounds;
    }

    /**
     * @param Will $will
     * @param Roller2d6DrdPlus $roller2d6DrdPlus
     * @return RollOnWillAgainstMalus
     */
    private function createRollAgainstMalusFromWounds(Will $will, Roller2d6DrdPlus $roller2d6DrdPlus)
    {
        return new RollOnWillAgainstMalus(new RollOnWill($will, $roller2d6DrdPlus->roll()));
    }

    /**
     * @return array|Pain[]
     */
    public function getPains()
    {
        $pains = [];
        foreach ($this->getAfflictions() as $affliction) {
            if (!($affliction instanceof Pain)) {
                continue;
            }
            $pains[] = $affliction;
        }

        return $pains;
    }

}