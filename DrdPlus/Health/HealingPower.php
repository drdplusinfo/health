<?php
namespace DrdPlus\Health;

use Drd\DiceRoll\Templates\Rollers\SpecificRolls\Roll2d6DrdPlus;
use DrdPlus\Codes\Body\ActivityAffectingHealingCode;
use DrdPlus\Codes\Body\ConditionsAffectingHealingCode;
use DrdPlus\Codes\RaceCode;
use DrdPlus\Codes\SubRaceCode;
use DrdPlus\Properties\Derived\Toughness;
use DrdPlus\Tables\Body\Healing\HealingByActivityTable;
use DrdPlus\Tables\Body\Healing\HealingByConditionsTable;
use DrdPlus\Tables\Body\Healing\HealingConditionsPercents;
use DrdPlus\Tables\Measurements\Wounds\Wounds as TableWounds;
use DrdPlus\Tables\Measurements\Wounds\Wounds;
use DrdPlus\Tables\Measurements\Wounds\WoundsBonus;
use DrdPlus\Tables\Measurements\Wounds\WoundsTable;
use DrdPlus\Tables\Races\RacesTable;
use Granam\Integer\IntegerInterface;
use Granam\Integer\Tools\ToInteger;
use Granam\Strict\Object\StrictObject;

class HealingPower extends StrictObject implements IntegerInterface
{
    /**
     * @var Wounds
     */
    private $healUpToWounds;

    /**
     * @param RaceCode $raceCode
     * @param SubRaceCode $subRaceCode
     * @param RacesTable $racesTable
     * @param ActivityAffectingHealingCode $activityCode
     * @param HealingByActivityTable $healingByActivityTable
     * @param ConditionsAffectingHealingCode $conditionsCode
     * @param HealingConditionsPercents $healingConditionsPercents
     * @param HealingByConditionsTable $healingByConditionsTable
     * @param Roll2d6DrdPlus $roll2d6DrdPlus
     * @param WoundsTable $woundsTable
     * @return HealingPower
     * @throws \DrdPlus\Tables\Body\Healing\Exceptions\UnknownCodeOfHealingInfluence
     * @throws \DrdPlus\Tables\Body\Healing\Exceptions\UnknownCodeOfHealingInfluence
     * @throws \DrdPlus\Tables\Body\Healing\Exceptions\UnexpectedHealingConditionsPercents
     */
    public static function createForRegeneration(
        RaceCode $raceCode,
        SubRaceCode $subRaceCode,
        RacesTable $racesTable,
        ActivityAffectingHealingCode $activityCode,
        HealingByActivityTable $healingByActivityTable,
        ConditionsAffectingHealingCode $conditionsCode,
        HealingConditionsPercents $healingConditionsPercents,
        HealingByConditionsTable $healingByConditionsTable,
        Roll2d6DrdPlus $roll2d6DrdPlus,
        WoundsTable $woundsTable
    )
    {
        /** see PPH page 80 right column */
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $healingPower =
            ($racesTable->hasNativeRegeneration($raceCode, $subRaceCode)
                ? 4
                : 0
            )
            + $healingByActivityTable->getHealingBonusByActivity($activityCode->getValue())
            + $healingByConditionsTable->getHealingBonusByConditions($conditionsCode->getValue(), $healingConditionsPercents)
            - 7
            + $roll2d6DrdPlus->getValue();

        return new static($healingPower, $woundsTable);
    }

    /**
     * @param int $healingPowerValue
     * @param WoundsTable $woundsTable
     * @return HealingPower
     */
    public static function createForTreatment($healingPowerValue, WoundsTable $woundsTable)
    {
        return new static($healingPowerValue, $woundsTable);
    }

    /**
     * @param int $healingPowerValue
     * @param WoundsTable $woundsTable
     */
    private function __construct($healingPowerValue, WoundsTable $woundsTable)
    {
        $this->healUpToWounds = (new WoundsBonus($healingPowerValue, $woundsTable))->getWounds();
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->healUpToWounds->getBonus()->getValue();
    }

    /**
     * @param Toughness $toughness
     * @return int
     */
    public function getHealUpTo(Toughness $toughness)
    {
        return $this->healUpToWounds->getValue() + $toughness->getValue();
    }

    /**
     * @param int $healedAmount not a healing power, but real amount of healed wound points
     * @param Toughness $toughness
     * @param WoundsTable $woundsTable
     * @return static|healingPower
     * @throws \DrdPlus\Health\Exceptions\HealedAmountIsTooBig
     * @throws \Granam\Integer\Tools\Exceptions\WrongParameterType
     * @throws \Granam\Integer\Tools\Exceptions\ValueLostOnCast
     */
    public function decreaseByHealedAmount($healedAmount, Toughness $toughness, WoundsTable $woundsTable)
    {
        $healedAmount = ToInteger::toInteger($healedAmount);
        $healUpTo = $this->getHealUpTo($toughness);
        if ($healedAmount > $healUpTo) {
            throw new Exceptions\HealedAmountIsTooBig(
                "So much amount {$healedAmount} could not be healed by this healing power"
                . " ({$this->getValue()}) able to heal only up to {$healUpTo}"
            );
        }
        if ($healedAmount === 0) {
            return $this;
        }
        $decreasedHealingPower = clone $this;
        $remainingHealUpTo = $healUpTo - $healedAmount - $toughness->getValue();
        $decreasedHealingPower->healUpToWounds = new TableWounds($remainingHealUpTo, $woundsTable);

        return $decreasedHealingPower;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getValue();
    }
}