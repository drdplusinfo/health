<?php
namespace DrdPlus\Health\Afflictions\ElementalPertinence;

use DrdPlus\Codes\ElementCodes;

class AirPertinence extends ElementalPertinence
{
    const AIR = ElementCodes::AIR;

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

}