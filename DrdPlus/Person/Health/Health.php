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
     * @var MalusFromWounds
     * @ORM\Column(type="malus_from_wounds")
     */
    private $malusFromWounds;
    /**
     * @var ReasonToRollAgainstMalus|null
     * @ORM\Column(type="reason_to_roll_against_malus", nullable=true)
     */
    private $reasonToRollAgainstMalus;
    /**
     * @var GridOfWounds|null is just a helper, does not need to be persisted
     */
    private $gridOfWounds;

    public function __construct(WoundsLimit $woundsLimit)
    {
        $this->wounds = new ArrayCollection();
        $this->woundsLimitValue = $woundsLimit->getValue();
        $this->afflictions = new ArrayCollection();
        $this->treatmentBoundary = TreatmentBoundary::getIt(0);
        $this->malusFromWounds = MalusFromWounds::getIt(0);
    }

    /**
     * @param WoundSize $woundSize
     * @param SpecificWoundOrigin $specificWoundOrigin Beware if the wound size is considered as serious than OrdinaryWoundOrigin will be used instead
     * @return OrdinaryWound|SeriousWound
     */
    public function createWound(WoundSize $woundSize, SpecificWoundOrigin $specificWoundOrigin)
    {
        $wound = $this->isSeriousInjury($woundSize)
            ? new SeriousWound($this, $woundSize, $specificWoundOrigin)
            : new OrdinaryWound($this, $woundSize);
        $this->wounds->add($wound);
        if ($wound->isSerious()) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $this->treatmentBoundary = TreatmentBoundary::getIt($this->getTreatmentBoundary()->getValue() + $wound->getValue());
        }
        $this->resolveMalusAfterWound($wound);

        return $wound;
    }

    /**
     * @param WoundSize $woundSize
     * @return bool
     */
    private function isSeriousInjury(WoundSize $woundSize)
    {
        return $this->getGridOfWounds()->calculateFilledHalfRowsFor($woundSize->getValue()) > 0;
    }

    /**
     * @return bool
     */
    private function maySufferFromPain()
    {
        // if person became unconscious than the roll against pain malus is not re-rolled
        return $this->getGridOfWounds()->getNumberOfFilledRows() >= GridOfWounds::PAIN_NUMBER_OF_ROWS && $this->isConscious();
    }

    /**
     * @return bool
     */
    public function isConscious()
    {
        return $this->getGridOfWounds()->getNumberOfFilledRows() < GridOfWounds::UNCONSCIOUS_NUMBER_OF_ROWS;
    }

    private function resolveMalusAfterWound(Wound $wound)
    {
        if ($wound->getValue() === 0) {
            return;
        }
        if ($this->maySufferFromPain()) {
            $this->reasonToRollAgainstMalus = ReasonToRollAgainstMalus::getWoundReason();
        } elseif ($this->isConscious()) {
            $this->malusFromWounds = MalusFromWounds::getIt(0);
        } // otherwise left the previous malus - person will suffer by it when comes conscious again
    }

    /**
     * Every serious injury SHOULD has at least one accompanying affliction (but it is PJ privilege to say it has not).
     * @param AfflictionByWound $afflictionByWound
     * @throws \DrdPlus\Person\Health\Exceptions\UnknownAfflictionOriginatingWound
     * @throws \DrdPlus\Person\Health\Exceptions\AfflictionIsAlreadyRegistered
     */
    public function addAffliction(AfflictionByWound $afflictionByWound)
    {
        if (!$this->doesHaveThatWound($afflictionByWound->getSeriousWound())) {
            throw new Exceptions\UnknownAfflictionOriginatingWound(
                "Given affliction '{$afflictionByWound->getName()}' to add comes from unknown wound"
                . " of value {$afflictionByWound->getSeriousWound()} and origin '{$afflictionByWound->getSeriousWound()->getWoundOrigin()}'."
                . ' Have you created that wound by current health?'
            );
        }
        if ($this->doesHaveThatAffliction($afflictionByWound)) {
            throw new Exceptions\AfflictionIsAlreadyRegistered(
                "Given instance of affliction '{$afflictionByWound->getName()}' is already added."
            );
        }
        $this->afflictions->add($afflictionByWound);
    }

    private function doesHaveThatWound(Wound $givenWound)
    {
        if ($givenWound->getHealth() !== $this) {
            return false; // easiest test - the wound belongs to different health
        }
        foreach ($this->wounds as $registeredWound) {
            if ($givenWound === $registeredWound) {
                return true; // this health recognizes that wound
            }
        }

        return false; // the wound know this health, but this health does not know that wound
    }

    private function doesHaveThatAffliction(AfflictionByWound $givenAffliction)
    {
        foreach ($this->afflictions as $registeredAffliction) {
            if ($givenAffliction === $registeredAffliction) {
                return true;
            }
        }

        return false;
    }

    /**
     * Also sets treatment boundary to unhealed wounds after. Even if the heal itself heals nothing!
     * @param HealingPower $healingPower
     * @return int amount of actually healed points of wounds
     */
    public function healNewOrdinaryWoundsUpTo(HealingPower $healingPower)
    {
        // can heal new and ordinary wounds only, up to limit by current treatment boundary
        $healedAmount = 0;
        foreach ($this->getUntreatedOrdinaryWounds() as $newOrdinaryWound) {
            if ($healingPower->getHealUpTo() > 0) { // we do not spent all the healing power
                $currentlyHealed = $newOrdinaryWound->heal($healingPower);
                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                $healingPower = $healingPower->decreaseByHealedAmount($currentlyHealed); // new instance
                $healedAmount += $currentlyHealed;
            }
            // all new ordinary wounds become "old", healed or not (and those unhealed can be healed only by a professional or nature itself)
            $newOrdinaryWound->setOld();
        }
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $this->treatmentBoundary = TreatmentBoundary::getIt($this->getUnhealedWoundsSum());
        $this->resolveMalusAfterHeal($healedAmount);

        return $healedAmount;
    }

    /**
     * @return OrdinaryWound[]|Collection
     */
    private function getUntreatedOrdinaryWounds()
    {
        return $this->wounds->filter(
            function (Wound $wound) {
                return !$wound->isHealed() && !$wound->isSerious() && !$wound->isOld();
            }
        );
    }

    private function resolveMalusAfterHeal($healedAmount)
    {
        if ($healedAmount === 0) { // both wounds remain the same and pain remains the same
            return;
        }
        if ($this->maySufferFromPain()) {
            $this->reasonToRollAgainstMalus = ReasonToRollAgainstMalus::getHealReason();
        } else if ($this->isConscious()) {
            $this->malusFromWounds = MalusFromWounds::getIt(0); // pain is gone and person feel it - lets remove the malus
        } // otherwise left the previous malus - person will suffer by it when comes conscious again
    }

    /**
     * @param SeriousWound $seriousWound
     * @param HealingPower $healingPower
     * @return int amount of healed points of wounds
     * @throws \DrdPlus\Person\Health\Exceptions\UnknownSeriousWoundToHeal
     * @throws \DrdPlus\Person\Health\Exceptions\ExpectedFreshWoundToHeal
     */
    public function healSeriousWound(SeriousWound $seriousWound, HealingPower $healingPower)
    {
        if (!$this->doesHaveThatWound($seriousWound)) {
            throw new Exceptions\UnknownSeriousWoundToHeal(
                "Given serious wound of value {$seriousWound->getValue()} and origin {$seriousWound->getWoundOrigin()} to heal does not belongs to this health"
            );
        }
        if ($seriousWound->isOld()) {
            throw new Exceptions\ExpectedFreshWoundToHeal(
                "Given serious wound of value {$seriousWound->getValue()} and origin {$seriousWound->getWoundOrigin()} should not be old to be healed."
            );
        }
        $healedAmount = $seriousWound->heal($healingPower);
        $seriousWound->setOld();
        // treatment boundary is taken with wounds down together
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $this->treatmentBoundary = TreatmentBoundary::getIt($this->treatmentBoundary->getValue() - $healedAmount);
        $this->resolveMalusAfterHeal($healedAmount);

        return $healedAmount;
    }

    /**
     * Regenerate any wound, both ordinary and serious, both new and old, by natural or unnatural way.
     * @param HealingPower $healingPower
     * @return int actually regenerated amount
     */
    public function regenerate(HealingPower $healingPower)
    {
        // every wound becomes old after this
        $regeneratedAmount = 0;
        foreach ($this->getUnhealedWounds() as $unhealedWound) {
            if ($healingPower->getHealUpTo() > 0) { // we do not spent all the healing power yet
                $currentlyRegenerated = $unhealedWound->heal($healingPower);
                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                $healingPower = $healingPower->decreaseByHealedAmount($currentlyRegenerated); // new instance
                $regeneratedAmount += $currentlyRegenerated;
            }
            // all unhealed wounds become "old", healed or not (and can be healed only by this regeneration)
            $unhealedWound->setOld();
        }
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $this->treatmentBoundary = TreatmentBoundary::getIt($this->getUnhealedWoundsSum());
        $this->resolveMalusAfterHeal($regeneratedAmount);

        return $regeneratedAmount;
    }

    /**
     * Usable for info about amount of wounds which can be healed by basic healing
     * @return int
     */
    public function getNewOrdinaryWoundsSum()
    {
        return array_sum(
            array_map(
                function (OrdinaryWound $ordinaryWound) {
                    return $ordinaryWound->getValue();
                },
                $this->getUntreatedOrdinaryWounds()->toArray()
            )
        );
    }

    /**
     * @return int
     */
    public function getUnhealedSeriousWoundsSum()
    {
        return array_sum(
            array_map(
                function (SeriousWound $seriousWound) {
                    return $seriousWound->getValue();
                },
                $this->getUnhealedSeriousWounds()->toArray()
            )
        );
    }

    /**
     * @return SeriousWound[]|Collection
     */
    private function getUnhealedSeriousWounds()
    {
        return $this->getUnhealedWounds()->filter( // creates new Collection instance
            function (Wound $wound) {
                return $wound->isSerious();
            }
        );
    }

    /**
     * @return int
     */
    public function getUnhealedWoundsSum()
    {
        return $this->getGridOfWounds()->getSumOfWounds();
    }

    /**
     * @return int
     */
    public function getHealthMaximum()
    {
        return $this->getWoundsLimitValue() * GridOfWounds::TOTAL_NUMBER_OF_ROWS;
    }

    /**
     * @return int
     */
    public function getRemainingHealthAmount()
    {
        return max(0, $this->getHealthMaximum() - $this->getUnhealedWoundsSum());
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gives both new and old wounds
     * @return Collection|Wound[]
     */
    public function getUnhealedWounds()
    {
        // results into different instance of Collection which avoids external change of the original
        return $this->wounds->filter(
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
     * Looking for a setter? Sorry but affliction can be caused only by a new wound.
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
     */
    public function changeWoundsLimit(WoundsLimit $woundsLimit)
    {
        if ($this->getWoundsLimitValue() === $woundsLimit->getValue()) {
            return;
        }
        $previousHealthMaximum = $this->getHealthMaximum();
        $this->woundsLimitValue = $woundsLimit->getValue();
        if ($previousHealthMaximum > $this->getHealthMaximum()) { // current wounds relatively increases
            $this->reasonToRollAgainstMalus = ReasonToRollAgainstMalus::getWoundReason();
        } elseif ($previousHealthMaximum < $this->getHealthMaximum()) { // current wounds relatively decreases
            $this->reasonToRollAgainstMalus = ReasonToRollAgainstMalus::getHealReason();
        }
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
        return $this->getUnhealedSeriousWounds()->count();
    }

    const DEADLY_NUMBER_OF_SERIOUS_INJURIES = 6;

    /**
     * @return bool
     */
    public function isAlive()
    {
        return
            $this->getRemainingHealthAmount() > 0
            && $this->getNumberOfSeriousInjuries() < self::DEADLY_NUMBER_OF_SERIOUS_INJURIES;
    }

    /**
     *
     * @return int
     */
    public function getSignificantMalus()
    {
        $maluses = [$this->getMalusCausedByWounds()];
        foreach ($this->getPains() as $pain) {
            // for Pain see PPH page 79, left column
            $maluses[] = $pain->getEffect()->getMalusFromPain($pain);
        }

        return max($maluses);
    }

    /**
     * @return int
     */
    public function getMalusCausedByWounds()
    {
        if ($this->needsToRollAgainstMalus()) {
            throw new \LogicException('Roll first');
        }
        if ($this->malusFromWounds === null // no roll against malus happened so far, therefore no malus at all
            || $this->getGridOfWounds()->getNumberOfFilledRows() === 0 // else even unconscious can has a malus (but would be wrong if applied)
        ) {
            return 0;
        }

        /**
         * note: Can grow only on new wound when reach second row in grid of wounds.
         * Can decrease only on heal of any wound when on second row in grid of wounds.
         * Is removed when first row of grid of wounds is not filled.
         * See PPH page 75 right column
         */
        return $this->malusFromWounds->getValue();
    }

    /**
     * @return bool
     */
    public function needsToRollAgainstMalus()
    {
        return $this->reasonToRollAgainstMalus !== null;
    }

    /**
     * @return ReasonToRollAgainstMalus|null
     */
    public function getReasonToRollAgainstMalus()
    {
        return $this->reasonToRollAgainstMalus;
    }

    /**
     * @param Will $will
     * @param Roller2d6DrdPlus $roller2d6DrdPlus
     * @return bool|MalusFromWounds
     */
    public function rollAgainstMalus(Will $will, Roller2d6DrdPlus $roller2d6DrdPlus)
    {
        if (!$this->needsToRollAgainstMalus()) {
            throw new \LogicException;
        }

        return $this->reasonToRollAgainstMalus->becauseOfHeal()
            ? $this->rollAgainstMalusOnHeal($will, $roller2d6DrdPlus)
            : $this->rollAgainstMalusOnWound($will, $roller2d6DrdPlus);
    }

    /**
     * @param Will $will
     * @param Roller2d6DrdPlus $roller2d6DrdPlus
     * @return bool|MalusFromWounds
     */
    private function rollAgainstMalusOnHeal(Will $will, Roller2d6DrdPlus $roller2d6DrdPlus)
    {
        if ($this->malusFromWounds->getValue() === 0) {
            return false;
        }
        $newRoll = $this->createRollOnWillAgainstMalus($will, $roller2d6DrdPlus);
        // lesser (or same of course) malus remains; can not be increased on healing
        if ($this->malusFromWounds->getValue() >= $newRoll->getMalusValue()) { // greater in mathematical meaning (malus is negative)
            return false;
        }

        return $this->malusFromWounds = MalusFromWounds::getIt($newRoll->getMalusValue());
    }

    /**
     * @param Will $will
     * @param Roller2d6DrdPlus $roller2d6DrdPlus
     * @return RollOnWillAgainstMalus
     */
    private function createRollOnWillAgainstMalus(Will $will, Roller2d6DrdPlus $roller2d6DrdPlus)
    {
        return new RollOnWillAgainstMalus(new RollOnWill($will, $roller2d6DrdPlus->roll()));
    }

    /**
     * @param Will $will
     * @param Roller2d6DrdPlus $roller2d6DrdPlus
     * @return bool|MalusFromWounds
     */
    private function rollAgainstMalusOnWound(Will $will, Roller2d6DrdPlus $roller2d6DrdPlus)
    {
        if ($this->malusFromWounds->getValue() === MalusFromWounds::MOST) {
            return false;
        }
        $newRoll = $this->createRollOnWillAgainstMalus($will, $roller2d6DrdPlus);
        // bigger (or same of course) malus remains; can not be decreased on new wounds
        if ($this->malusFromWounds->getValue() <= $newRoll->getMalusValue() // lesser in mathematical meaning (malus is negative)
        ) {
            return false;
        }

        return $this->malusFromWounds = MalusFromWounds::getIt($newRoll->getMalusValue());
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