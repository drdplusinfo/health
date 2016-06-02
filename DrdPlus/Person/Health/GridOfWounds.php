<?php
namespace DrdPlus\Person\Health;

use DrdPlus\Tools\Calculations\SumAndRound;
use Granam\Strict\Object\StrictObject;

class GridOfWounds extends StrictObject
{

    const PAIN_NUMBER_OF_ROWS = 1;
    const UNCONSCIOUS_NUMBER_OF_ROWS = 2;
    const TOTAL_NUMBER_OF_ROWS = 3;

    /**
     * @var Health
     */
    private $health;

    public function __construct(Health $health)
    {
        $this->health = $health;
    }

    /**
     * @return int
     */
    public function getWoundsPerRowMaximum()
    {
        return $this->health->getWoundBoundaryValue();
    }

    /**
     * @param int $woundValue
     * @return int
     */
    public function calculateFilledHalfRowsFor($woundValue)
    {
        if ($this->getWoundsPerRowMaximum() % 2 === 0) { // odd
            $filledHalfRows = SumAndRound::floor($woundValue / ($this->getWoundsPerRowMaximum() / 2));
        } else {
            // first half round up, second down (for example 11 = 6 + 5)
            $halves = [SumAndRound::ceiledHalf($this->getWoundsPerRowMaximum()), SumAndRound::flooredHalf($this->getWoundsPerRowMaximum())];
            $filledHalfRows = 0;
            while ($woundValue > 0) {
                foreach ($halves as $half) {
                    $woundValue -= $half;
                    if ($woundValue < 0) {
                        break;
                    }
                    $filledHalfRows++;
                }
            }
        }

        return $filledHalfRows < (self::TOTAL_NUMBER_OF_ROWS * 2)
            ? $filledHalfRows
            : self::TOTAL_NUMBER_OF_ROWS * 2; // to prevent "more dead than death" value
    }

    /**
     * @return int
     */
    public function getNumberOfFilledRows()
    {
        $numberOfFilledRows = SumAndRound::floor($this->health->getUnhealedWoundsSum() / $this->getWoundsPerRowMaximum());

        return $numberOfFilledRows < self::TOTAL_NUMBER_OF_ROWS
            ? $numberOfFilledRows
            : self::TOTAL_NUMBER_OF_ROWS;
    }

}