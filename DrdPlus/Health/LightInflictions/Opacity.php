<?php
namespace DrdPlus\Health\LightInflictions;

use DrdPlus\Tables\Measurements\Amount\AmountBonus;
use DrdPlus\Tables\Measurements\Amount\AmountTable;
use DrdPlus\Tables\Measurements\Distance\Distance;
use Granam\Integer\IntegerInterface;
use Granam\Integer\PositiveInteger;
use Granam\Integer\Tools\ToInteger;
use Granam\Strict\Object\StrictObject;

class Opacity extends StrictObject implements PositiveInteger
{
    /**
     * @var int
     */
    private $value;

    /**
     * @param IntegerInterface $barrierDensity
     * @param Distance $barrierLength
     * @param AmountTable $amountTable
     * @return Opacity
     */
    public static function createFromBarrierDensity(
        IntegerInterface $barrierDensity,
        Distance $barrierLength,
        AmountTable $amountTable
    )
    {
        return new self(
            (new AmountBonus(
                $barrierDensity->getValue() + $barrierLength->getBonus()->getValue(),
                $amountTable
            ))->getAmount()->getValue()
        );
    }

    /**
     * @return Opacity
     */
    public static function createTransparent()
    {
        return new self(0);
    }

    /**
     * @param IntegerInterface|int $value
     */
    private function __construct($value)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $this->value = ToInteger::toInteger($value);
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
        return (string)$this->getValue();
    }

    /**
     * @return int
     */
    public function getVisibilityMalus()
    {
        if ($this->getValue() > 0) {
            return -$this->getValue();
        }

        return 0;
    }

}