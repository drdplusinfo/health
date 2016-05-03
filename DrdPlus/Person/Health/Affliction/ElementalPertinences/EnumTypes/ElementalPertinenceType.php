<?php
namespace DrdPlus\Person\Health\Affliction\ElementalPertinences\EnumTypes;

use Doctrineum\String\StringEnumType;
use DrdPlus\Person\Health\Affliction\ElementalPertinences\AirPertinence;
use DrdPlus\Person\Health\Affliction\ElementalPertinences\EarthPertinence;
use DrdPlus\Person\Health\Affliction\ElementalPertinences\FirePertinence;
use DrdPlus\Person\Health\Affliction\ElementalPertinences\WaterPertinence;

class ElementalPertinenceType extends StringEnumType
{
    public static function registerAllPertinence()
    {
        self::registerSelf();
        self::addSubTypeEnum(FirePertinence::class, FirePertinence::FIRE);
        self::addSubTypeEnum(WaterPertinence::class, WaterPertinence::WATER);
        self::addSubTypeEnum(EarthPertinence::class, EarthPertinence::EARTH);
        self::addSubTypeEnum(AirPertinence::class, AirPertinence::AIR);
    }
}