<?php
namespace DrdPlus\Person\Health\Afflictions\ElementalPertinence;

use DrdPlus\Codes\ElementCodes;

class WaterPertinence extends ElementalPertinence
{
    const WATER = ElementCodes::WATER;

    /**
     * @return WaterPertinence
     */
    public static function getMinus()
    {
        return parent::getMinus();
    }

    /**
     * @return WaterPertinence
     */
    public static function getPlus()
    {
        return parent::getPlus();
    }

}