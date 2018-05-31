<?php
namespace DrdPlus\Health;

use DrdPlus\DiceRolls\Templates\Rolls\Roll2d6DrdPlus;
use DrdPlus\Codes\Body\ActivityAffectingHealingCode;
use DrdPlus\Codes\Body\ConditionsAffectingHealingCode;
use DrdPlus\Codes\RaceCode;
use DrdPlus\Codes\SubRaceCode;
use DrdPlus\Properties\Derived\Toughness;
use DrdPlus\Tables\Body\Healing\HealingConditionsPercents;
use DrdPlus\Tables\Measurements\Wounds\WoundsBonus;
use DrdPlus\Tables\Tables;
use Granam\Integer\IntegerInterface;
use Granam\Strict\Object\StrictObject;

class HealingPower extends StrictObject implements IntegerInterface
{
    /** @var int */
    private $value;
    /** @var int */
    private $healUpToWounds;

    /**
     * @param RaceCode $raceCode
     * @param SubRaceCode $subRaceCode
     * @param Toughness $toughness
     * @param ActivityAffectingHealingCode $activityAffectingHealingCode
     * @param ConditionsAffectingHealingCode $conditionsAffectingHealingCode
     * @param HealingConditionsPercents $healingConditionsPercents
     * @param Roll2d6DrdPlus $roll2d6DrdPlus
     * @param Tables $tables
     * @return HealingPower
     * @throws \DrdPlus\Tables\Body\Healing\Exceptions\UnknownCodeOfHealingInfluence
     * @throws \DrdPlus\Tables\Body\Healing\Exceptions\UnknownCodeOfHealingInfluence
     * @throws \DrdPlus\Tables\Body\Healing\Exceptions\UnexpectedHealingConditionsPercents
     */
    public static function createForRegeneration(
        RaceCode $raceCode,
        SubRaceCode $subRaceCode,
        Toughness $toughness,
        ActivityAffectingHealingCode $activityAffectingHealingCode,
        ConditionsAffectingHealingCode $conditionsAffectingHealingCode,
        HealingConditionsPercents $healingConditionsPercents,
        Roll2d6DrdPlus $roll2d6DrdPlus,
        Tables $tables
    ): HealingPower
    {
        /** see PPH page 80 right column */
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $healingPower =
            ($tables->getRacesTable()->hasNativeRegeneration($raceCode, $subRaceCode)
                ? 4
                : 0
            )
            + $tables->getHealingByActivityTable()->getHealingBonusByActivity($activityAffectingHealingCode->getValue())
            + $tables->getHealingByConditionsTable()->getHealingBonusByConditions($conditionsAffectingHealingCode->getValue(), $healingConditionsPercents)
            - 7 // constant value coming from a official formula
            + $roll2d6DrdPlus->getValue();

        return new static($healingPower, $toughness, $tables);
    }

    /**
     * @param int $healingPowerValue
     * @param Toughness $toughness
     * @param Tables $tables
     * @return HealingPower
     */
    public static function createForTreatment(int $healingPowerValue, Toughness $toughness, Tables $tables): HealingPower
    {
        return new static($healingPowerValue, $toughness, $tables);
    }

    /**
     * @param int $healingPowerValue
     * @param Toughness $toughness
     * @param Tables $tables
     */
    private function __construct(int $healingPowerValue, Toughness $toughness, Tables $tables)
    {
        $this->value = $healingPowerValue + $toughness->getValue();
        $this->healUpToWounds = (new WoundsBonus($this->value, $tables->getWoundsTable()))->getWounds()->getValue();
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     *
     * @return int
     */
    public function getHealUpToWounds(): int
    {
        return $this->healUpToWounds;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getValue() . " (with heal up to {$this->getHealUpToWounds()})";
    }
}