<?php
namespace DrdPlus\Health;

use DrdPlus\Tables\Measurements\Wounds\Wounds as TableWounds;
use DrdPlus\Tables\Measurements\Wounds\WoundsBonus;
use DrdPlus\Tables\Measurements\Wounds\WoundsTable;
use Granam\Integer\IntegerInterface;
use Granam\Integer\Tools\ToInteger;
use Granam\Strict\Object\StrictObject;

class HealingPower extends StrictObject implements IntegerInterface
{
    /**
     * @var TableWounds
     */
    private $healUpToWounds;
    /**
     * @var WoundsTable
     */
    private $woundsTable;

    /**
     * HealingPower constructor.
     * @param int $healingPowerValue
     * @param WoundsTable $woundsTable
     */
    public function __construct($healingPowerValue, WoundsTable $woundsTable)
    {
        $this->healUpToWounds = (new WoundsBonus($healingPowerValue, $woundsTable))->getWounds();
        $this->woundsTable = $woundsTable;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->healUpToWounds->getBonus()->getValue();
    }

    /**
     * @return int
     */
    public function getHealUpTo()
    {
        return $this->healUpToWounds->getValue();
    }

    /**
     * @param int $healedAmount not a healing power, but real amount of healed wound points
     * @return static|healingPower
     * @throws \DrdPlus\Health\Exceptions\HealedAmountIsTooBig
     * @throws \Granam\Integer\Tools\Exceptions\WrongParameterType
     * @throws \Granam\Integer\Tools\Exceptions\ValueLostOnCast
     */
    public function decreaseByHealedAmount($healedAmount)
    {
        $healedAmount = ToInteger::toInteger($healedAmount);
        if ($healedAmount > $this->getHealUpTo()) {
            throw new Exceptions\HealedAmountIsTooBig(
                "So much amount {$healedAmount} could not be healed by this healing power ({$this->getValue()}) able to heal only up to {$this->getHealUpTo()}"
            );
        }
        if ($healedAmount === 0) {
            return $this;
        }
        $remainingHealUpTo = $this->getHealUpTo() - $healedAmount;
        $decreasedHealingPower = clone $this;
        $decreasedHealingPower->healUpToWounds = new TableWounds($remainingHealUpTo, $this->woundsTable);

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