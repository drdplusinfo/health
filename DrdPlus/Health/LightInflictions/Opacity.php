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
     * @param Distance $barrierDistance
     * @param AmountTable $amountTable
     * @return Opacity
     */
    public static function createFromBarrierDensity(
        IntegerInterface $barrierDensity,
        Distance $barrierDistance,
        AmountTable $amountTable
    )
    {
        return new self(
            (new AmountBonus($barrierDensity->getValue() + $barrierDistance->getBonus()->getValue(), $amountTable))
                ->getAmount()->getValue()
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
     * @return float|int
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

}