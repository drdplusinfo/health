<?php
namespace DrdPlus\Person\Health\Afflictions\ElementalPertinence;

use DrdPlus\Codes\ElementCodes;

class EarthPertinence extends ElementalPertinence
{
    const EARTH = ElementCodes::EARTH;

    /**
     * @return EarthPertinence
     */
    public static function getMinus()
    {
        return parent::getMinus();
    }

    /**
     * @return EarthPertinence
     */
    public static function getPlus()
    {
        return parent::getPlus();
    }

}