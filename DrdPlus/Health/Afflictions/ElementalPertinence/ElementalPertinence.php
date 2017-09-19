<?php
namespace DrdPlus\Health\Afflictions\ElementalPertinence;

use Doctrineum\String\StringEnum;
use Granam\String\StringTools;

/**
 * @method static ElementalPertinence getEnum($enumValue)
 */
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
    public static function getPertinenceCode(): string
    {
        return preg_replace('~_pertinence$~', '', StringTools::camelCaseToSnakeCasedBasename(static::class));
    }

    /**
     * @return bool
     */
    public function isMinus(): bool
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
    public function isPlus(): bool
    {
        return strpos($this->getValue(), self::PLUS) === 0;
    }

}