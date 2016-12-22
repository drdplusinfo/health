<?php
namespace DrdPlus\Health\LightInflictions;

use DrdPlus\Tools\Calculations\SumAndRound;
use Granam\Integer\PositiveInteger;
use Granam\Strict\Object\StrictObject;

class Contrast extends StrictObject implements PositiveInteger
{
    /**
     * @var int
     */
    private $value;
    /**
     * @var bool
     */
    private $fromLightToDark;

    /**
     * @param LightingQuality $previousLightQuality
     * @param LightingQuality $currentLightQuality
     */
    public function __construct(LightingQuality $previousLightQuality, LightingQuality $currentLightQuality)
    {
        $difference = $previousLightQuality->getValue() - $currentLightQuality->getValue();
        $this->fromLightToDark = $difference > 0; // if previous light was more intensive than current, then it comes darker
        // see PPH page 128 left column
        if ($difference > 0) {
            $this->value = abs(SumAndRound::floor($difference / 10));
        } else {
            $this->value = abs(SumAndRound::ceil($difference / 10));
        }
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getValue() . ($this->isFromLightToDark() ? ' (to dark)' : ' (to light)');
    }

    /**
     * @return bool
     */
    public function isFromLightToDark()
    {
        return $this->fromLightToDark;
    }

    /**
     * @return bool
     */
    public function isFromDarkToLight()
    {
        return !$this->isFromLightToDark();
    }
}