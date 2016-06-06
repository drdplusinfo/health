<?php
namespace DrdPlus\Person\Health\Afflictions\ElementalPertinence;

use Doctrineum\String\StringEnum;
use Granam\String\StringTools;

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
     * @return string
     */
    public static function getPertinenceCode()
    {
        return preg_replace('~_pertinence$~', '', StringTools::camelCaseToSnakeCasedBasename(static::class));
    }

    /**
     * @return bool
     */
    public function isMinus()
    {
        return strpos($this->getValue(), self::MINUS) === 0;
    }

    const PLUS = '+';

    /**
     * @return ElementalPertinence
     */
    protected static function getPlus()
    {
        return static::getEnum(self::PLUS . static::getPertinenceCode());
    }

    /**
     * @return bool
     */
    public function isPlus()
    {
        return strpos($this->getValue(), self::PLUS) === 0;
    }

}