<?php
namespace DrdPlus\Person\Health;

use Granam\Integer\IntegerObject;
use Granam\Tools\ValueDescriber;

class WoundSize extends IntegerObject
{
    /**
     * @param $value
     * @return WoundSize
     * @throws \Granam\Integer\Tools\Exceptions\WrongParameterType
     * @throws \DrdPlus\Person\Health\Exceptions\WoundSizeCanNotBeNegative
     */
    public static function createIt($value)
    {
        return new static($value);
    }

    /**
     * @param mixed $value
     * @throws \Granam\Integer\Tools\Exceptions\WrongParameterType
     * @throws \DrdPlus\Person\Health\Exceptions\WoundSizeCanNotBeNegative
     */
    public function __construct($value)
    {
        parent::__construct($value);

        if ($this->getValue() < 0) {
            throw new Exceptions\WoundSizeCanNotBeNegative(
                'Expected at least zero, got ' . ValueDescriber::describe($value)
            );
        }
    }
}