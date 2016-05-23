<?php
namespace DrdPlus\Person\Health;

use DrdPlus\Tables\Measurements\Wounds\WoundsBonus;
use DrdPlus\Tables\Measurements\Wounds\WoundsTable;
use Granam\Integer\IntegerInterface;
use Granam\Strict\Object\StrictObject;

class HealingPower extends StrictObject implements IntegerInterface
{
    /**
     * @var int
     */
    private $woundsBonus;

    /**
     * HealingPower constructor.
     * @param int $value
     * @param WoundsTable $woundsTable
     */
    public function __construct($value, WoundsTable $woundsTable)
    {
        $this->woundsBonus = new WoundsBonus($value, $woundsTable);
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->woundsBonus->getValue();
    }

    /**
     * @return int
     */
    public function getHealUpTo()
    {
        return $this->woundsBonus->getWounds()->getValue();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getValue();
    }
}