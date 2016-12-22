<?php
namespace DrdPlus\Health\LightInflictions;

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
     */
    public function __construct(LightingQuality $currentLightingQuality)
    {
        $this->malus = 0;
        if ($currentLightingQuality->getValue() < -10) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $contrast = new Contrast(new LightingQuality(0), $currentLightingQuality);
            $this->malus = -$contrast->getValue();
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