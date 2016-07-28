<?php
namespace DrdPlus\Health;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrineum\Entity\Entity;
use Drd\DiceRoll\Templates\Rollers\Roller2d6DrdPlus;
use DrdPlus\Health\Afflictions\AfflictionByWound;
use DrdPlus\Health\Afflictions\SpecificAfflictions\Pain;
use DrdPlus\Properties\Base\Will;
use DrdPlus\Properties\Derived\Toughness;
use DrdPlus\Properties\Derived\WoundBoundary;
use DrdPlus\RollsOn\Traps\RollOnWillAgainstMalus;
use DrdPlus\RollsOn\Traps\RollOnWill;
use DrdPlus\Tables\Measurements\Wounds\WoundsTable;
use Granam\Strict\Object\StrictObject;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
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
     * @ORM\OneToMany(targetEntity="\DrdPlus\Health\Afflictions\AfflictionByWound", mappedBy="health", cascade={"all"}, orphanRemoval=true)
     */
    private $afflictions;
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
     * @var ReasonToRollAgainstWoundMalus|null
     * @ORM\Column(type="reason_to_roll_against_wound_malus", nullable=true)
     */
    private $reasonToRollAgainstWoundMalus;
    /**
     * @var GridOfWounds|null is just a helper, does not need to be persisted
     */
    private $gridOfWounds;
    /**
     * @var bool helper to avoid side-adding of new wounds (Those created on their own and linked by Doctrine relation instead of directly here).
     */
    private $openForNewWound = false;

    public function __construct()
    {
        $this->wounds = new ArrayCollection();
        $this->afflictions = new ArrayCollection();
        $this->treatmentBoundary = TreatmentBoundary::getIt(0);
        $this->malusFromWounds = MalusFromWounds::getIt(0);
    }

    /**
     * @param WoundSize $woundSize
     * @param SeriousWoundOrigin $seriousWoundOrigin Beware if the wound size is considered as serious than OrdinaryWoundOrigin will be used instead
     * @param WoundBoundary $woundBoundary
     * @return OrdinaryWound|SeriousWound
     * @throws \DrdPlus\Health\Exceptions\NeedsToRollAgainstMalusFirst
     */
    public function createWound(
        WoundSize $woundSize,
        SeriousWoundOrigin $seriousWoundOrigin,
        WoundBoundary $woundBoundary
    )
    {
        $this->checkIfNeedsToRollAgainstMalusFirst();
        $this->openForNewWound = true;
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $wound = $this->isSeriousInjury($woundSize, $woundBoundary)
            ? new SeriousWound($this, $woundSize, $seriousWoundOrigin)
            : new OrdinaryWound($this, $woundSize);
        $this->openForNewWound = false;
        $this->wounds->add($wound);
        if ($wound->isSerious()) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $this->treatmentBoundary = TreatmentBoundary::getIt($this->getTreatmentBoundary()->getValue() + $wound->getValue());
        }
        $this->resolveMalusAfterWound($wound->getValue(), $woundBoundary);

        return $wound;
    }

    /**
     * @throws \DrdPlus\Health\Exceptions\NeedsToRollAgainstMalusFirst
     */
    private function checkIfNeedsToRollAgainstMalusFirst()
    {
        if ($this->needsToRollAgainstMalus()) {
            throw new Exceptions\NeedsToRollAgainstMalusFirst(
                'Need to roll on will against malus caused by wounds because of previous '
                . $this->reasonToRollAgainstWoundMalus
            );
        }
    }

    /**
     * @return boolean
     */
    public function isOpenForNewWound()
    {
        return $this->openForNewWound;
    }

    /**
     * @param WoundSize $woundSize
     * @param WoundBoundary $woundBoundary
     * @return bool
     */
    private function isSeriousInjury(WoundSize $woundSize, WoundBoundary $woundBoundary)
    {
        return $this->getGridOfWounds()->calculateFilledHalfRowsFor($woundSize, $woundBoundary) > 0;
    }

    /**
     * @param WoundBoundary $woundBoundary
     * @return bool
     */
    private function maySufferFromPain(WoundBoundary $woundBoundary)
    {
        // if the being became unconscious than the roll against pain malus is not re-rolled
        return
            $this->getGridOfWounds()->getNumberOfFilledRows($woundBoundary) >= GridOfWounds::PAIN_NUMBER_OF_ROWS
            && $this->isConscious($woundBoundary);
    }

    /**
     * @param WoundBoundary $woundBoundary
     * @return bool
     */
    public function isConscious(WoundBoundary $woundBoundary)
    {
        return $this->getGridOfWounds()->getNumberOfFilledRows($woundBoundary) < GridOfWounds::UNCONSCIOUS_NUMBER_OF_ROWS;
    }

    /**
     * @param int $woundAmount
     * @param WoundBoundary $woundBoundary
     */
    private function resolveMalusAfterWound($woundAmount, WoundBoundary $woundBoundary)
    {
        if ($woundAmount === 0) {
            return;
        }
        if ($this->maySufferFromPain($woundBoundary)) {
            $this->reasonToRollAgainstWoundMalus = ReasonToRollAgainstWoundMalus::getWoundReason();
        } elseif ($this->isConscious($woundBoundary)) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $this->malusFromWounds = MalusFromWounds::getIt(0);
        } // otherwise left the previous malus - creature will suffer by it when comes conscious again
    }

    /**
     * Every serious injury SHOULD has at least one accompanying affliction (but it is PJ privilege to say it has not).
     * @param AfflictionByWound $afflictionByWound
     * @throws \DrdPlus\Health\Exceptions\UnknownAfflictionOriginatingWound
     * @throws \DrdPlus\Health\Exceptions\AfflictionIsAlreadyRegistered
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
     * @param Toughness $toughness
     * @param WoundsTable $woundsTable
     * @return int amount of actually healed points of wounds
     * @throws \DrdPlus\Health\Exceptions\NeedsToRollAgainstMalusFirst
     */
    public function healNewOrdinaryWoundsUpTo(HealingPower $healingPower, Toughness $toughness, WoundsTable $woundsTable)
    {
        $this->checkIfNeedsToRollAgainstMalusFirst();
        // can heal new and ordinary wounds only, up to limit by current treatment boundary
        $healedAmount = 0;
        foreach ($this->getUnhealedOrdinaryWounds() as $newOrdinaryWound) {
            if ($healingPower->getHealUpTo($toughness) > 0) { // we do not spent all the healing power
                $currentlyHealed = $newOrdinaryWound->heal($healingPower, $toughness);
                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                $healingPower = $healingPower->decreaseByHealedAmount($currentlyHealed, $toughness, $woundsTable); // new instance
                $healedAmount += $currentlyHealed;
            }
            // all new ordinary wounds become "old", healed or not (and those unhealed can be healed only by a professional or nature itself)
            $newOrdinaryWound->setOld();
        }
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $this->treatmentBoundary = TreatmentBoundary::getIt($this->getUnhealedWoundsSum());
        $this->resolveMalusAfterHeal($healedAmount, new WoundBoundary($toughness, $woundsTable));

        return $healedAmount;
    }

    /**
     * @return OrdinaryWound[]|Collection
     */
    private function getUnhealedOrdinaryWounds()
    {
        return $this->wounds->filter(
            function (Wound $wound) {
                return !$wound->isHealed() && !$wound->isSerious() && !$wound->isOld();
            }
        );
    }

    /**
     * @param WoundBoundary $woundBoundary
     * @param int $healedAmount
     */
    private function resolveMalusAfterHeal($healedAmount, WoundBoundary $woundBoundary)
    {
        if ($healedAmount === 0) { // both wounds remain the same and pain remains the same
            return;
        }
        if ($this->maySufferFromPain($woundBoundary)) {
            $this->reasonToRollAgainstWoundMalus = ReasonToRollAgainstWoundMalus::getHealReason();
        } else if ($this->isConscious($woundBoundary)) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $this->malusFromWounds = MalusFromWounds::getIt(0); // pain is gone and creature feel it - lets remove the malus
        } // otherwise left the previous malus - creature will suffer by it when comes conscious again
    }

    /**
     * @param SeriousWound $seriousWound
     * @param HealingPower $healingPower
     * @param Toughness $toughness
     * @param WoundsTable $woundsTable
     * @return int amount of healed points of wounds
     * @throws \DrdPlus\Health\Exceptions\UnknownSeriousWoundToHeal
     * @throws \DrdPlus\Health\Exceptions\ExpectedFreshWoundToHeal
     * @throws \DrdPlus\Health\Exceptions\NeedsToRollAgainstMalusFirst
     */
    public function healSeriousWound(
        SeriousWound $seriousWound,
        HealingPower $healingPower,
        Toughness $toughness,
        WoundsTable $woundsTable
    )
    {
        $this->checkIfNeedsToRollAgainstMalusFirst();
        if (!$this->doesHaveThatWound($seriousWound)) {
            throw new Exceptions\UnknownSeriousWoundToHeal(
                "Given serious wound of value {$seriousWound->getValue()} and origin"
                . " {$seriousWound->getWoundOrigin()} to heal does not belongs to this health"
            );
        }
        if ($seriousWound->isOld()) {
            throw new Exceptions\ExpectedFreshWoundToHeal(
                "Given serious wound of value {$seriousWound->getValue()} and origin"
                . " {$seriousWound->getWoundOrigin()} should not be old to be healed."
            );
        }
        $healedAmount = $seriousWound->heal($healingPower, $toughness);
        $seriousWound->setOld();
        // treatment boundary is taken with wounds down together
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $this->treatmentBoundary = TreatmentBoundary::getIt($this->treatmentBoundary->getValue() - $healedAmount);
        $this->resolveMalusAfterHeal($healedAmount, new WoundBoundary($toughness, $woundsTable));

        return $healedAmount;
    }

    /**
     * Regenerate any wound, both ordinary and serious, both new and old, by natural or unnatural way.
     * @param HealingPower $healingPower
     * @param Toughness $toughness
     * @param WoundsTable $woundsTable
     * @return int actually regenerated amount
     * @throws \DrdPlus\Health\Exceptions\NeedsToRollAgainstMalusFirst
     */
    public function regenerate(
        HealingPower $healingPower,
        Toughness $toughness,
        WoundsTable $woundsTable
    )
    {
        $this->checkIfNeedsToRollAgainstMalusFirst();
        // every wound becomes old after this
        $regeneratedAmount = 0;
        foreach ($this->getUnhealedWounds() as $unhealedWound) {
            if ($healingPower->getHealUpTo($toughness) > 0) { // we do not spent all the healing power yet
                $currentlyRegenerated = $unhealedWound->heal($healingPower, $toughness);
                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                $healingPower = $healingPower->decreaseByHealedAmount($currentlyRegenerated, $toughness, $woundsTable); // new instance
                $regeneratedAmount += $currentlyRegenerated;
            }
            // all unhealed wounds become "old", healed or not (and can be healed only by regenerating like this)
            $unhealedWound->setOld();
        }
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $this->treatmentBoundary = TreatmentBoundary::getIt($this->getUnhealedWoundsSum());
        $this->resolveMalusAfterHeal($regeneratedAmount, new WoundBoundary($toughness, $woundsTable));

        return $regeneratedAmount;
    }

    /**
     * Usable for info about amount of wounds which can be healed by basic healing
     * @return int
     */
    public function getUnhealedNewOrdinaryWoundsSum()
    {
        return array_sum(
            array_map(
                function (OrdinaryWound $ordinaryWound) {
                    return $ordinaryWound->getValue();
                },
                $this->getUnhealedOrdinaryWounds()->toArray()
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
        return array_sum(
            array_map(
                function (Wound $unhealedWound) {
                    return $unhealedWound->getValue();
                },
                $this->getUnhealedWounds()->toArray()
            )
        );
    }

    /**
     * @param WoundBoundary $woundBoundary
     * @return int
     */
    public function getHealthMaximum(WoundBoundary $woundBoundary)
    {
        return $woundBoundary->getValue() * GridOfWounds::TOTAL_NUMBER_OF_ROWS;
    }

    /**
     * @param WoundBoundary $woundBoundary
     * @return int
     */
    public function getRemainingHealthAmount(WoundBoundary $woundBoundary)
    {
        return max(0, $this->getHealthMaximum($woundBoundary) - $this->getUnhealedWoundsSum());
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
     * @param WoundBoundary $woundBoundary
     * @return bool
     */
    public function isAlive(WoundBoundary $woundBoundary)
    {
        return
            $this->getRemainingHealthAmount($woundBoundary) > 0
            && $this->getNumberOfSeriousInjuries() < self::DEADLY_NUMBER_OF_SERIOUS_INJURIES;
    }

    /**
     * @param WoundBoundary $woundBoundary
     * @return int
     * @throws \DrdPlus\Health\Exceptions\NeedsToRollAgainstMalusFirst
     */
    public function getSignificantMalus(WoundBoundary $woundBoundary)
    {
        $maluses = [$this->getMalusFromWoundsValue($woundBoundary)];
        foreach ($this->getPains() as $pain) {
            // for Pain see PPH page 79, left column
            $maluses[] = $pain->getMalus();
        }

        return min($maluses); // the most significant malus, therefore the lowest value
    }

    /**
     * @param WoundBoundary $woundBoundary
     * @return int
     * @throws \DrdPlus\Health\Exceptions\NeedsToRollAgainstMalusFirst
     */
    private function getMalusFromWoundsValue(WoundBoundary $woundBoundary)
    {
        $this->checkIfNeedsToRollAgainstMalusFirst();
        if ($this->getGridOfWounds()->getNumberOfFilledRows($woundBoundary) === 0) {
            return 0;
        }

        /**
         * note: Can grow only on new wound when reach second row in grid of wounds.
         * Can decrease only on heal of any wound when on second row in grid of wounds.
         * Is removed when first row of grid of wounds is not filled.
         * Even unconscious can has a malus (but would be wrong if applied).
         * See PPH page 75 right column
         */
        return $this->malusFromWounds->getValue();
    }

    /**
     * @return bool
     */
    public function needsToRollAgainstMalus()
    {
        return $this->reasonToRollAgainstWoundMalus !== null;
    }

    /**
     * @return ReasonToRollAgainstWoundMalus|null
     */
    public function getReasonToRollAgainstWoundMalus()
    {
        return $this->reasonToRollAgainstWoundMalus;
    }

    /**
     * @param Will $will
     * @param Roller2d6DrdPlus $roller2d6DrdPlus
     * @param WoundBoundary $woundBoundary
     * @return int resulted malus
     * @throws \DrdPlus\Health\Exceptions\UselessRollAgainstMalus
     */
    public function rollAgainstMalusFromWounds(
        Will $will,
        Roller2d6DrdPlus $roller2d6DrdPlus,
        WoundBoundary $woundBoundary
    )
    {
        if (!$this->needsToRollAgainstMalus()) {
            throw new Exceptions\UselessRollAgainstMalus(
                'There is no need to roll against malus from wounds'
                . ($this->isConscious($woundBoundary) ? '' : ' (being is unconscious)')
            );
        }

        $malusValue = $this->reasonToRollAgainstWoundMalus->becauseOfHeal()
            ? $this->rollAgainstMalusOnHeal($will, $roller2d6DrdPlus)
            : $this->rollAgainstMalusOnWound($will, $roller2d6DrdPlus);

        $this->reasonToRollAgainstWoundMalus = null;

        return $malusValue;
    }

    /**
     * @param Will $will
     * @param Roller2d6DrdPlus $roller2d6DrdPlus
     * @return int
     */
    private function rollAgainstMalusOnHeal(Will $will, Roller2d6DrdPlus $roller2d6DrdPlus)
    {
        if ($this->malusFromWounds->getValue() === 0) {
            return $this->malusFromWounds->getValue(); // on heal can be the malus only lowered - there is nothing to lower
        }
        $newRoll = $this->createRollOnWillAgainstMalus($will, $roller2d6DrdPlus);
        // lesser (or same of course) malus remains; can not be increased on healing
        if ($this->malusFromWounds->getValue() >= $newRoll->getMalusValue()) { // greater in mathematical meaning (malus is negative)
            return $this->malusFromWounds->getValue(); // lesser malus remains
        }
        $malusFromWounds = $this->setMalusFromWounds($newRoll);

        return $malusFromWounds->getValue();
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
     * @param RollOnWillAgainstMalus $rollOnWillAgainstMalus
     * @return MalusFromWounds
     */
    private function setMalusFromWounds(RollOnWillAgainstMalus $rollOnWillAgainstMalus)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return $this->malusFromWounds = MalusFromWounds::getIt($rollOnWillAgainstMalus->getMalusValue());
    }

    /**
     * @param Will $will
     * @param Roller2d6DrdPlus $roller2d6DrdPlus
     * @return int
     */
    private function rollAgainstMalusOnWound(Will $will, Roller2d6DrdPlus $roller2d6DrdPlus)
    {
        if ($this->malusFromWounds->getValue() === MalusFromWounds::MOST) {
            return $this->malusFromWounds->getValue();
        }
        $newRoll = $this->createRollOnWillAgainstMalus($will, $roller2d6DrdPlus);
        // bigger (or same of course) malus remains; can not be decreased on new wounds
        if ($this->malusFromWounds->getValue() <= $newRoll->getMalusValue() // lesser in mathematical meaning (malus is negative)
        ) {
            return $this->malusFromWounds->getValue(); // greater malus remains
        }

        return $this->setMalusFromWounds($newRoll)->getValue();
    }

    /**
     * @return Collection|Pain[]
     */
    public function getPains()
    {
        $pains = new ArrayCollection();
        foreach ($this->getAfflictions() as $affliction) {
            if (!($affliction instanceof Pain)) {
                continue;
            }
            $pains->add($affliction);
        }

        return $pains;
    }

}