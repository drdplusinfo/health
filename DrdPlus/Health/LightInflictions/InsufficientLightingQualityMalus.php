<?php
namespace DrdPlus\Health\LightInflictions;

use DrdPlus\Codes\RaceCode;
use DrdPlus\Skills\Combined\DuskSight;
use Granam\Integer\NegativeInteger;
use Granam\Strict\Object\StrictObject;

/**
 * See PPH page 128
 * Note about Orcs: they can have malus even from too strong lighting, despite the class name "insufficient".
 */
class InsufficientLightingQualityMalus extends StrictObject implements NegativeInteger
{
    /**
     * @var int
     */
    private $malus;

    /**
     * @param LightingQuality $currentLightingQuality
     * @param RaceCode $raceCode
     * @param DuskSight $duskSight
     */
    public function __construct(
        LightingQuality $currentLightingQuality,
        RaceCode $raceCode,
        DuskSight $duskSight
    )
    {
        $this->malus = 0;
        if ($currentLightingQuality->getValue() < -10) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $contrast = new Contrast(new LightingQuality(0), $currentLightingQuality);
            $possibleMalus = -$contrast->getValue();
            if (in_array($raceCode->getValue(), [RaceCode::DWARF, RaceCode::ORC], true)) {
                $possibleMalus += 4; // lowering malus
            } else if ($raceCode->getValue() === RaceCode::KROLL) {
                $possibleMalus += 2; // lowering malus
            }
            $possibleMalus += $duskSight->getInsufficientLightingBonus(); // lowering malus
            if ($possibleMalus >= -20) {
                $this->malus = $possibleMalus;
            } else {
                $this->malus = -20; // maximal possible malus on absolute dark, see PPH page 128 right column bottom
            }
        } else if ($currentLightingQuality->getValue() >= 60 /* strong daylight */ && $raceCode->getValue() === RaceCode::ORC) {
            // see PPH page 128 right column bottom
            $this->malus = -2;
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