<?php
namespace DrdPlus\Health\LightInflictions;

use DrdPlus\Codes\RaceCode;
use Granam\Integer\NegativeInteger;
use Granam\Strict\Object\StrictObject;

class InsufficientLightingQualityMalus extends StrictObject implements NegativeInteger
{
    /**
     * @var int
     */
    private $malus;

    /**
     * @param LightingQuality $currentLightingQuality
     * @param RaceCode $raceCode
     */
    public function __construct(LightingQuality $currentLightingQuality, RaceCode $raceCode)
    {
        $this->malus = 0;
        if ($currentLightingQuality->getValue() < -10) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $contrast = new Contrast(new LightingQuality(0), $currentLightingQuality);
            $possibleMalus = -$contrast->getValue();
            if (in_array($raceCode->getValue(), [RaceCode::DWARF, RaceCode::ORC], true)) {
                $possibleMalus += 4;
            } else if ($raceCode->getValue() === RaceCode::KROLL) {
                $possibleMalus += 2;
            }
            if ($possibleMalus >= -20) {
                $this->malus = $possibleMalus;
            } else {
                $this->malus = -20;
            }
        }
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->malus;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getValue();
    }

}