<?php
namespace DrdPlus\Person\Health\Afflictions\ElementalPertinence;

use DrdPlus\Codes\ElementCodes;

class EarthPertinence extends ElementalPertinence
{
    const EARTH = ElementCodes::EARTH;

    /**
     * @return AirPertinence
     */
    public static function getMinus()
    {
        return parent::getMinus();
    }

    /**
     * @return AirPertinence
     */
    public static function getPlus()
    {
        return parent::getPlus();
    }

    /**
     * @return string
     */
    protected static function getPertinenceCode()
    {
        return self::EARTH;
    }

}