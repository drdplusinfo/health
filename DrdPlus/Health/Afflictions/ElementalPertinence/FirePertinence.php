<?php
namespace DrdPlus\Health\Afflictions\ElementalPertinence;

use DrdPlus\Codes\ElementCode;

class FirePertinence extends ElementalPertinence
{
    const FIRE = ElementCode::FIRE;

    /**
     * @return FirePertinence|ElementalPertinence
     */
    public static function getMinus()
    {
        return parent::getMinus();
    }

    /**
     * @return FirePertinence|ElementalPertinence
     */
    public static function getPlus()
    {
        return parent::getPlus();
    }

}