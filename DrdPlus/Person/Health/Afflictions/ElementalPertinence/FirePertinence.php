<?php
namespace DrdPlus\Person\Health\Afflictions\ElementalPertinence;

use DrdPlus\Codes\ElementCodes;

class FirePertinence extends ElementalPertinence
{
    const FIRE = ElementCodes::FIRE;

    /**
     * @return FirePertinence
     */
    public static function getMinus()
    {
        return parent::getMinus();
    }

    /**
     * @return FirePertinence
     */
    public static function getPlus()
    {
        return parent::getPlus();
    }

}