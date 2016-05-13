<?php
namespace DrdPlus\Person\Health\Afflictions\ElementalPertinence;

use Doctrineum\String\StringEnum;

abstract class ElementalPertinence extends StringEnum
{
    const MINUS = '-';

    /**
     * @return ElementalPertinence
     */
    protected static function getMinus()
    {
        return static::getEnum(self::MINUS . static::getPertinenceCode());
    }

    /**
     * @throws \LogicException
     */
    protected static function getPertinenceCode()
    {
        throw new \LogicException('Not implemented. Overload this method in child.');
    }

    const PLUS = '+';

    /**
     * @return ElementalPertinence
     */
    protected static function getPlus()
    {
        return static::getEnum(self::PLUS . static::getPertinenceCode());
    }
}