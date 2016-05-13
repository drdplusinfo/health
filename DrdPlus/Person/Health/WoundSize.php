<?php
namespace DrdPlus\Person\Health;

use Granam\Integer\IntegerObject;
use Granam\Tools\ValueDescriber;

class WoundSize extends IntegerObject
{
    public function __construct($value)
    {
        try {
            $finalValue = parent::__construct($value);
        } catch (\Granam\Integer\Tools\Exceptions\Exception $conversionException) {
            throw new Exceptions\WoundValueHasToBeAtLeastZero(
                'Expected integer as a wound value, got ' . ValueDescriber::describe($value),
                $conversionException->getCode(),
                $conversionException
            );
        }

        if ($finalValue < 0) {
            throw new Exceptions\WoundValueHasToBeAtLeastZero(
                'Expected at least zero, got ' . ValueDescriber::describe($finalValue)
            );
        }
    }
}