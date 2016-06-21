<?php
namespace DrdPlus\Health\Afflictions\ElementalPertinence;

use DrdPlus\Codes\ElementCode;

class EarthPertinence extends ElementalPertinence
{
    const EARTH = ElementCode::EARTH;

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