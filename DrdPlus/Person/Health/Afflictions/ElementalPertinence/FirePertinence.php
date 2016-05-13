<?php
namespace DrdPlus\Person\Health\Afflictions\ElementalPertinence;

use DrdPlus\Codes\ElementCodes;

class FirePertinence extends ElementalPertinence
{
    const FIRE = ElementCodes::FIRE;

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
        return self::FIRE;
    }

}