<?php
namespace DrdPlus\Health\Afflictions\ElementalPertinence;

use DrdPlus\Codes\ElementCode;

class AirPertinence extends ElementalPertinence
{
    const AIR = ElementCode::AIR;

    /**
     * @return AirPertinence|ElementalPertinence
     */
    public static function getMinus()
    {
        return parent::getMinus();
    }

    /**
     * @return AirPertinence|ElementalPertinence
     */
    public static function getPlus()
    {
        return parent::getPlus();
    }

}