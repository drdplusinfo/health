<?php
namespace DrdPlus\Person\Health\Affliction\ElementalPertinences;

use Doctrineum\String\StringEnum;

abstract class ElementalPertinence extends StringEnum
{
    const MINUS = '-';

    /**
     * @return ElementalPertinence
     */
    public static function getMinus()
    {
        return static::getEnum(self::MINUS);
    }

    const PLUS = '+';

    /**
     * @return ElementalPertinence
     */
    public static function getPlus()
    {
        return static::getEnum(self::PLUS);
    }
}