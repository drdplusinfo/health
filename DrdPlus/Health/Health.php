<?php
declare(strict_types=1); // on PHP 7+ are standard PHP methods strict to types of given parameters

namespace DrdPlus\Health;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrineum\Entity\Entity;
use DrdPlus\Codes\Body\SeriousWoundOriginCode;
use DrdPlus\DiceRolls\Templates\Rolls\Roll2d6DrdPlus;
use DrdPlus\Health\Afflictions\Affliction;
use DrdPlus\Health\Afflictions\AfflictionByWound;
use DrdPlus\Health\Afflictions\SpecificAfflictions\Pain;
use DrdPlus\Health\Inflictions\Glared;
use DrdPlus\Lighting\Glare;
use DrdPlus\Properties\Base\Will;
use DrdPlus\Properties\Derived\Toughness;
use DrdPlus\Properties\Derived\WoundBoundary;
use DrdPlus\RollsOn\Traps\RollOnWillAgainstMalus;
use DrdPlus\RollsOn\Traps\RollOnWill;
use DrdPlus\Tables\Tables;
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
     * @var ArrayCollection|Affliction[]
     * @ORM\OneToMany(targetEntity="\DrdPlus\Health\Afflictions\Affliction", mappedBy="health", cascade={"all"},
     *     orphanRemoval=true)
     */
    private $afflictions;
    /**
     * Separates new and old (or serious) injuries.
     *
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
     * @var bool helper to avoid side-adding of new wounds (Those created on their own and linked by Doctrine relation
     *     instead of directly here).
     */
    private $openForNewWound = false;
    /**
     * @var Glared
     * @ORM\OneToOne(targetEntity="\DrdPlus\Health\Inflictions\Glared", inversedBy="health", cascade={"all"}, fetch="EAGER")
     */
    private $glared;

    public function __construct()
    {
        $this->wounds = new ArrayCollection();
        $this->afflictions = new ArrayCollection();
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $this->treatmentBoundary = TreatmentBoundary::getIt(0);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $this->malusFromWounds = MalusFromWounds::getIt(0);
        $this->glared = Glared::createWithoutGlare($this);
    }

    /**
     * @param WoundSize $woundSize
     * @param SeriousWoundOriginCode $seriousWoundOriginCode Beware that if the wound size is considered as NOT serious then
     *     OrdinaryWoundOrigin will be used instead (as the only possible for @see OrdinaryWound)
     * @param WoundBoundary $woundBoundary
     * @return OrdinaryWound|SeriousWound|Wound
     * @throws \DrdPlus\Health\Exceptions\NeedsToRollAgainstMalusFirst
     */
    public function createWound(
        WoundSize $woundSize,
        SeriousWoundOriginCode $seriousWoundOriginCode,
        WoundBoundary $woundBoundary
    ): Wound
    {
        $this->checkIfNeedsToRollAgainstMalusFirst();
        $this->openForNewWound = true;
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $wound = $this->isSeriousInjury($woundSize, $woundBoundary)
            ? new SeriousWound($this, $woundSize, $seriousWoundOriginCode)
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
     * A lock of current health instance to ensure that new wound is created by that health,
     * @see \DrdPlus\Health\Wound::checkIfCreatedByGivenHealth
     * @return bool
     */
    public function isOpenForNewWound(): bool
    {
        return $this->openForNewWound;
    }

    private function isSeriousInjury(WoundSize $woundSize, WoundBoundary $woundBoundary): bool
    {
        return $this->getGridOfWounds()->calculateFilledHalfRowsFor($woundSize, $woundBoundary) > 0;
    }

    private function maySufferFromPain(WoundBoundary $woundBoundary): bool
    {
        // if the being became unconscious than the roll against pain malus is not re-rolled
        return
            $this->getGridOfWounds()->getNumberOfFilledRows($woundBoundary) >= GridOfWounds::PAIN_NUMBER_OF_ROWS
            && $this->isConscious($woundBoundary);
    }

    public function isConscious(WoundBoundary $woundBoundary): bool
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
     *
     * @param Affliction $affliction
     * @throws \DrdPlus\Health\Exceptions\UnknownAfflictionOriginatingWound
     * @throws \DrdPlus\Health\Exceptions\AfflictionIsAlreadyRegistered
     */
    public function addAffliction(Affliction $affliction): void
    {
        if ($affliction instanceof AfflictionByWound && !$this->doesHaveThatWound($affliction->getSeriousWound())) {
            throw new Exceptions\UnknownAfflictionOriginatingWound(
                "Given affliction '{$affliction->getName()}' to add comes from unknown wound"
                . " of value {$affliction->getSeriousWound()} and origin '{$affliction->getSeriousWound()->getWoundOriginCode()}'."
                . ' Have you created that wound by current health?'
            );
        }
        if ($this->doesHaveThatAffliction($affliction)) {
            throw new Exceptions\AfflictionIsAlreadyRegistered(
                "Given instance of affliction '{$affliction->getName()}' is already added."
            );
        }
        $this->afflictions->add($affliction);
    }

    /**
     * @param Wound $givenWound
     * @return bool
     */
    private function doesHaveThatWound(Wound $givenWound): bool
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

    /**
     * @param Affliction $givenAffliction
     * @return bool
     */
    private function doesHaveThatAffliction(Affliction $givenAffliction): bool
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
     *
     * @param HealingPower $healingPower
     * @param Toughness $toughness
     * @param Tables $tables
     * @return int amount of actually healed points of wounds
     * @throws \DrdPlus\Health\Exceptions\NeedsToRollAgainstMalusFirst
     */
    public function healFreshOrdinaryWounds(HealingPower $healingPower, Toughness $toughness, Tables $tables): int
    {
        $this->checkIfNeedsToRollAgainstMalusFirst();
        // can heal new and ordinary wounds only, up to limit by current treatment boundary
        $healedAmount = 0;
        foreach ($this->getUnhealedFreshOrdinaryWounds() as $newOrdinaryWound) {
            if ($healingPower->getHealUpTo($toughness) > 0) { // we do not spent all the healing power
                $currentlyHealed = $newOrdinaryWound->heal($healingPower, $toughness);
                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                $healingPower = $healingPower->decreaseByHealedAmount($currentlyHealed, $toughness, $tables); // new instance
                $healedAmount += $currentlyHealed;
            }
            // all new ordinary wounds become "old", healed or not (and those unhealed can be healed only by a professional or nature itself)
            $newOrdinaryWound->setOld();
        }
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $this->treatmentBoundary = TreatmentBoundary::getIt($this->getUnhealedWoundsSum());
        $this->resolveMalusAfterHeal($healedAmount, WoundBoundary::getIt($toughness, $tables));

        return $healedAmount;
    }

    /**
     * @return OrdinaryWound[]|Collection
     */
    private function getUnhealedFreshOrdinaryWounds()
    {
        return $this->wounds->filter(
            function (Wound $wound) {
                return !$wound->isHealed() && !$wound->isSerious() && !$wound->isOld();
            }
        );
    }

    /**
     * @return OrdinaryWound[]|Collection
     */
    private function getUnhealedFreshSeriousWounds()
    {
        return $this->wounds->filter(
            function (Wound $wound) {
                return !$wound->isHealed() && $wound->isSerious() && !$wound->isOld();
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
        } elseif ($this->isConscious($woundBoundary)) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $this->malusFromWounds = MalusFromWounds::getIt(0); // pain is gone and creature feel it - lets remove the malus
        } // otherwise left the previous malus - creature will suffer by it when comes conscious again
    }

    /**
     * @param SeriousWound $seriousWound
     * @param HealingPower $healingPower
     * @param Toughness $toughness
     * @param Tables $tables
     * @return int amount of healed points of wounds
     * @throws \DrdPlus\Health\Exceptions\UnknownSeriousWoundToHeal
     * @throws \DrdPlus\Health\Exceptions\ExpectedFreshWoundToHeal
     * @throws \DrdPlus\Health\Exceptions\NeedsToRollAgainstMalusFirst
     */
    public function healFreshSeriousWound(
        SeriousWound $seriousWound,
        HealingPower $healingPower,
        Toughness $toughness,
        Tables $tables
    ): int
    {
        $this->checkIfNeedsToRollAgainstMalusFirst();
        if (!$this->doesHaveThatWound($seriousWound)) {
            throw new Exceptions\UnknownSeriousWoundToHeal(
                "Given serious wound of value {$seriousWound->getValue()} and origin"
                . " {$seriousWound->getWoundOriginCode()} to heal does not belongs to this health"
            );
        }
        if ($seriousWound->isOld()) {
            throw new Exceptions\ExpectedFreshWoundToHeal(
                "Given serious wound of value {$seriousWound->getValue()} and origin"
                . " {$seriousWound->getWoundOriginCode()} should not be old to be healed."
            );
        }
        $healedAmount = $seriousWound->heal($healingPower, $toughness);
        $seriousWound->setOld();
        // treatment boundary is taken with wounds down together
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $this->treatmentBoundary = TreatmentBoundary::getIt($this->treatmentBoundary->getValue() - $healedAmount);
        $this->resolveMalusAfterHeal($healedAmount, WoundBoundary::getIt($toughness, $tables));

        return $healedAmount;
    }

    /**
     * Regenerate any wound, both ordinary and serious, both new and old, by natural or unnatural way.
     *
     * @param HealingPower $healingPower
     * @param Toughness $toughness
     * @param Tables $tables
     * @return int actually regenerated amount
     * @throws \DrdPlus\Health\Exceptions\NeedsToRollAgainstMalusFirst
     */
    public function regenerate(HealingPower $healingPower, Toughness $toughness, Tables $tables): int
    {
        $this->checkIfNeedsToRollAgainstMalusFirst();
        // every wound becomes old after this
        $regeneratedAmount = 0;
        foreach ($this->getUnhealedWounds() as $unhealedWound) {
            if ($healingPower->getHealUpTo($toughness) > 0) { // we do not spent all the healing power yet
                $currentlyRegenerated = $unhealedWound->heal($healingPower, $toughness);
                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                $healingPower = $healingPower->decreaseByHealedAmount($currentlyRegenerated, $toughness, $tables); // new instance
                $regeneratedAmount += $currentlyRegenerated;
            }
            // all unhealed wounds become "old", healed or not (and can be healed only by regenerating like this)
            $unhealedWound->setOld();
        }
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $this->treatmentBoundary = TreatmentBoundary::getIt($this->getUnhealedWoundsSum());
        $this->resolveMalusAfterHeal($regeneratedAmount, WoundBoundary::getIt($toughness, $tables));

        return $regeneratedAmount;
    }

    /**
     * Usable for info about amount of wounds which can be healed by basic healing
     *
     * @return int
     */
    public function getUnhealedFreshOrdinaryWoundsSum(): int
    {
        return \array_sum(
            array_map(
                function (OrdinaryWound $ordinaryWound) {
                    return $ordinaryWound->getValue();
                },
                $this->getUnhealedFreshOrdinaryWounds()->toArray()
            )
        );
    }

    /**
     * Usable for info about amount of wounds which can be healed by treatment
     *
     * @return int
     */
    public function getUnhealedFreshSeriousWoundsSum(): int
    {
        return \array_sum(
            array_map(
                function (SeriousWound $seriousWound) {
                    return $seriousWound->getValue();
                },
                $this->getUnhealedFreshSeriousWounds()->toArray()
            )
        );
    }

    /**
     * @return int
     */
    public function getUnhealedOrdinaryWoundsSum(): int
    {
        return \array_sum(
            array_map(
                function (OrdinaryWound $seriousWound) {
                    return $seriousWound->getValue();
                },
                $this->getUnhealedOrdinaryWounds()->toArray()
            )
        );
    }

    /**
     * @return int
     */
    public function getUnhealedSeriousWoundsSum(): int
    {
        return \array_sum(
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
    private function getUnhealedOrdinaryWounds(): Collection
    {
        return $this->getUnhealedWounds()->filter( // creates new Collection instance
            function (Wound $wound) {
                return !$wound->isSerious() && !$wound->isHealed();
            }
        );
    }

    /**
     * @return SeriousWound[]|Collection
     */
    private function getUnhealedSeriousWounds(): Collection
    {
        return $this->getUnhealedWounds()->filter( // creates new Collection instance
            function (Wound $wound) {
                return $wound->isSerious() && !$wound->isHealed();
            }
        );
    }

    /**
     * @return int
     */
    public function getUnhealedWoundsSum(): int
    {
        return \array_sum(
            \array_map(
                function (Wound $unhealedWound) {
                    return $unhealedWound->getValue();
                },
                $this->getUnhealedWounds()->toArray()
            )
        );
    }

    /**
     * @return int
     */
    public function getUnhealedFreshWoundsSum(): int
    {
        return \array_sum(
            \array_map(
                function (Wound $unhealedWound) {
                    return $unhealedWound->getValue();
                },
                $this->getUnhealedFreshWounds()->toArray()
            )
        );
    }

    /**
     * @return int
     */
    public function getUnhealedOldWoundsSum(): int
    {
        return \array_sum(
            \array_map(
                function (Wound $unhealedWound) {
                    return $unhealedWound->getValue();
                },
                $this->getUnhealedOldWounds()->toArray()
            )
        );
    }

    /**
     * @return int
     */
    public function getUnhealedOldSeriousWoundsSum(): int
    {
        return \array_sum(
            \array_map(
                function (Wound $unhealedWound) {
                    return $unhealedWound->getValue();
                },
                $this->getUnhealedOldSeriousWounds()->toArray()
            )
        );
    }

    private function getUnhealedOldSeriousWounds()
    {
        return $this->getUnhealedOldWounds()->filter(function (Wound $wound) {
            return $wound->isSerious() && $wound->isOld() && !$wound->isHealed();
        });
    }

    /**
     * @return int
     */
    public function getUnhealedOldOrdinaryWoundsSum(): int
    {
        return \array_sum(
            \array_map(
                function (Wound $unhealedWound) {
                    return $unhealedWound->getValue();
                },
                $this->getUnhealedOldOrdinaryWounds()->toArray()
            )
        );
    }

    private function getUnhealedOldOrdinaryWounds()
    {
        return $this->getUnhealedOldWounds()->filter(function (Wound $wound) {
            return !$wound->isSerious() && $wound->isOld() && !$wound->isHealed();
        });
    }

    /**
     * Can be healed only by regeneration.
     * @return Collection
     */
    public function getUnhealedOldWounds(): Collection
    {
        return $this->getUnhealedWounds()->filter(function (Wound $wound) {
            return $wound->isOld();
        });
    }

    /**
     * Can be healed by treatment.
     * @return Collection
     */
    public function getUnhealedFreshWounds(): Collection
    {
        return $this->getUnhealedWounds()->filter(function (Wound $wound) {
            return !$wound->isOld();
        });
    }

    /**
     * @param WoundBoundary $woundBoundary
     * @return int
     */
    public function getHealthMaximum(WoundBoundary $woundBoundary): int
    {
        return $woundBoundary->getValue() * GridOfWounds::TOTAL_NUMBER_OF_ROWS;
    }

    /**
     * @param WoundBoundary $woundBoundary
     * @return int
     */
    public function getRemainingHealthAmount(WoundBoundary $woundBoundary): int
    {
        return max(0, $this->getHealthMaximum($woundBoundary) - $this->getUnhealedWoundsSum());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Gives both fresh and old wounds
     *
     * @return Collection|Wound[]
     */
    public function getUnhealedWounds(): Collection
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
    public function getGridOfWounds(): GridOfWounds
    {
        if ($this->gridOfWounds === null) {
            $this->gridOfWounds = new GridOfWounds($this);
        }

        return $this->gridOfWounds;
    }

    /**
     * Looking for a setter? Sorry but affliction can be caused only by a new wound.
     *
     * @return Collection|AfflictionByWound[]
     */
    public function getAfflictions(): Collection
    {
        return clone $this->afflictions; // cloned to avoid external change of the collection
    }

    /**
     * @return int
     */
    public function getStrengthMalusFromAfflictions(): int
    {
        $strengthMalus = 0;
        foreach ($this->getAfflictions() as $afflictionByWound) {
            $strengthMalus += $afflictionByWound->getStrengthMalus();
        }

        return $strengthMalus;
    }

    /**
     * @return int
     */
    public function getAgilityMalusFromAfflictions(): int
    {
        $agilityMalus = 0;
        foreach ($this->getAfflictions() as $afflictionByWound) {
            $agilityMalus += $afflictionByWound->getAgilityMalus();
        }

        return $agilityMalus;
    }

    /**
     * @return int
     */
    public function getKnackMalusFromAfflictions(): int
    {
        $knackMalus = 0;
        foreach ($this->getAfflictions() as $afflictionByWound) {
            $knackMalus += $afflictionByWound->getKnackMalus();
        }

        return $knackMalus;
    }

    /**
     * @return int
     */
    public function getWillMalusFromAfflictions(): int
    {
        $willMalus = 0;
        foreach ($this->getAfflictions() as $afflictionByWound) {
            $willMalus += $afflictionByWound->getWillMalus();
        }

        return $willMalus;
    }

    /**
     * @return int
     */
    public function getIntelligenceMalusFromAfflictions(): int
    {
        $intelligenceMalus = 0;
        foreach ($this->getAfflictions() as $afflictionByWound) {
            $intelligenceMalus += $afflictionByWound->getIntelligenceMalus();
        }

        return $intelligenceMalus;
    }

    /**
     * @return int
     */
    public function getCharismaMalusFromAfflictions(): int
    {
        $charismaMalus = 0;
        foreach ($this->getAfflictions() as $afflictionByWound) {
            $charismaMalus += $afflictionByWound->getCharismaMalus();
        }

        return $charismaMalus;
    }

    /**
     * Treatment boundary is set automatically on any heal (lowering wounds) or new serious injury
     *
     * @return TreatmentBoundary
     */
    public function getTreatmentBoundary(): TreatmentBoundary
    {
        return $this->treatmentBoundary;
    }

    /**
     * @return int
     */
    public function getNumberOfSeriousInjuries(): int
    {
        return $this->getUnhealedSeriousWounds()->count();
    }

    private const DEADLY_NUMBER_OF_SERIOUS_INJURIES = 6;

    /**
     * @param WoundBoundary $woundBoundary
     * @return bool
     */
    public function isAlive(WoundBoundary $woundBoundary): bool
    {
        return
            $this->getRemainingHealthAmount($woundBoundary) > 0
            && $this->getNumberOfSeriousInjuries() < self::DEADLY_NUMBER_OF_SERIOUS_INJURIES;
    }

    /**
     * Dominant, applied malus from wounds (pains respectively)
     * @param WoundBoundary $woundBoundary
     * @return int
     * @throws \DrdPlus\Health\Exceptions\NeedsToRollAgainstMalusFirst
     */
    public function getSignificantMalusFromPains(WoundBoundary $woundBoundary): int
    {
        $maluses = [$this->getMalusFromWoundsValue($woundBoundary)];
        foreach ($this->getPains() as $pain) {
            // for Pain see PPH page 79, left column
            $maluses[] = $pain->getMalusToActivities();
        }

        return \min($maluses); // the most significant malus (always lesser than zero), therefore the lowest value
    }

    /**
     * @param WoundBoundary $woundBoundary
     * @return int
     * @throws \DrdPlus\Health\Exceptions\NeedsToRollAgainstMalusFirst
     */
    private function getMalusFromWoundsValue(WoundBoundary $woundBoundary): int
    {
        $this->checkIfNeedsToRollAgainstMalusFirst();
        if (!$this->mayHaveMalusFromWounds($woundBoundary)) {
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
    public function needsToRollAgainstMalus(): bool
    {
        return $this->reasonToRollAgainstWoundMalus !== null;
    }

    /**
     * @return ReasonToRollAgainstWoundMalus|null
     */
    public function getReasonToRollAgainstWoundMalus(): ?ReasonToRollAgainstWoundMalus
    {
        return $this->reasonToRollAgainstWoundMalus;
    }

    /**
     * @param Will $will
     * @param Roll2d6DrdPlus $roll2D6DrdPlus
     * @param WoundBoundary $woundBoundary
     * @return int resulted malus
     * @throws \DrdPlus\Health\Exceptions\UselessRollAgainstMalus
     */
    public function rollAgainstMalusFromWounds(
        Will $will,
        Roll2d6DrdPlus $roll2D6DrdPlus,
        WoundBoundary $woundBoundary
    ): int
    {
        if (!$this->needsToRollAgainstMalus()) {
            throw new Exceptions\UselessRollAgainstMalus(
                'There is no need to roll against malus from wounds'
                . ($this->isConscious($woundBoundary) ? '' : ' (being is unconscious)')
            );
        }

        $malusValue = $this->reasonToRollAgainstWoundMalus->becauseOfHeal()
            ? $this->rollAgainstMalusOnHeal($will, $roll2D6DrdPlus)
            : $this->rollAgainstMalusOnWound($will, $roll2D6DrdPlus);

        $this->reasonToRollAgainstWoundMalus = null;

        return $malusValue;
    }

    /**
     * @param Will $will
     * @param Roll2d6DrdPlus $roll2D6DrdPlus
     * @return int
     */
    private function rollAgainstMalusOnHeal(Will $will, Roll2d6DrdPlus $roll2D6DrdPlus): int
    {
        if ($this->malusFromWounds->getValue() === 0) {
            return $this->malusFromWounds->getValue(); // on heal can be the malus only lowered - there is nothing to lower
        }
        $newRoll = $this->createRollOnWillAgainstMalus($will, $roll2D6DrdPlus);
        // lesser (or same of course) malus remains; can not be increased on healing
        if ($this->malusFromWounds->getValue() >= $newRoll->getMalusValue()) { // greater in mathematical meaning (malus is negative)
            return $this->malusFromWounds->getValue(); // lesser malus remains
        }
        $malusFromWounds = $this->setMalusFromWounds($newRoll);

        return $malusFromWounds->getValue();
    }

    /**
     * @param Will $will
     * @param Roll2d6DrdPlus $roll2D6DrdPlus
     * @return RollOnWillAgainstMalus
     */
    private function createRollOnWillAgainstMalus(Will $will, Roll2d6DrdPlus $roll2D6DrdPlus): RollOnWillAgainstMalus
    {
        return new RollOnWillAgainstMalus(new RollOnWill($will, $roll2D6DrdPlus));
    }

    /**
     * @param RollOnWillAgainstMalus $rollOnWillAgainstMalus
     * @return MalusFromWounds
     */
    private function setMalusFromWounds(RollOnWillAgainstMalus $rollOnWillAgainstMalus): MalusFromWounds
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return $this->malusFromWounds = MalusFromWounds::getIt($rollOnWillAgainstMalus->getMalusValue());
    }

    /**
     * @param Will $will
     * @param Roll2d6DrdPlus $roll2D6DrdPlus
     * @return int
     */
    private function rollAgainstMalusOnWound(Will $will, Roll2d6DrdPlus $roll2D6DrdPlus): int
    {
        if ($this->malusFromWounds->getValue() === MalusFromWounds::MOST) {
            return $this->malusFromWounds->getValue();
        }
        $newRoll = $this->createRollOnWillAgainstMalus($will, $roll2D6DrdPlus);
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
    public function getPains(): Collection
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

    /**
     * @param Glare $glare
     */
    public function inflictByGlare(Glare $glare)
    {
        $this->glared = Glared::createFromGlare($glare, $this);
    }

    /**
     * @return Glared
     */
    public function getGlared(): Glared
    {
        return $this->glared;
    }

    public function hasFreshWounds(): bool
    {
        return $this->getUnhealedWounds()->exists(function (/** @noinspection PhpUnusedParameterInspection */
            int $index, Wound $wound) {
            return !$wound->isOld();
        });
    }

    /**
     * @param WoundBoundary $woundBoundary
     * @return bool
     */
    public function mayHaveMalusFromWounds(WoundBoundary $woundBoundary): bool
    {
        return $this->getGridOfWounds()->getNumberOfFilledRows($woundBoundary) > 0;
    }
}