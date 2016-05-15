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
    public function getSumOfWounds()
    {
        return count($this->getPointsOfWounds()); // presumes each point of value 1 and only 1
    }

    /**
     * @return array|PointOfWound[]
     */
    private function getPointsOfWounds()
    {
        $pointsOfWounds = [];
        foreach ($this->health->getUnhealedWounds() as $unhealedWound) {
            foreach ($unhealedWound->getPointsOfWound() as $pointOfWound) {
                $pointsOfWounds[] = $pointOfWound;
            }
        }

        return $pointsOfWounds;
    }

    /**
     * @return int
     */
    public function getWoundsPerRowMaximum()
    {
        return $this->health->getWoundsLimitValue();
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
     * @param int $woundValue
     * @return bool
     */
    public function isSeriousInjury($woundValue)
    {
        return $this->calculateFilledHalfRowsFor($woundValue) > 0;
    }

    /**
     * @return int
     */
    public function getHealthMaximum()
    {
        return $this->health->getWoundsLimitValue() * self::TOTAL_NUMBER_OF_ROWS;
    }

    /**
     * @return int
     */
    public function getRemainingHealth()
    {
        return max(0, $this->getHealthMaximum() - $this->getSumOfWounds());
    }

    /**
     * @return int
     */
    public function getNumberOfFilledRows()
    {
        $numberOfFilledRows = SumAndRound::floor($this->getSumOfWounds() / $this->getWoundsPerRowMaximum());

        return $numberOfFilledRows < self::TOTAL_NUMBER_OF_ROWS
            ? $numberOfFilledRows
            : self::TOTAL_NUMBER_OF_ROWS;
    }

}