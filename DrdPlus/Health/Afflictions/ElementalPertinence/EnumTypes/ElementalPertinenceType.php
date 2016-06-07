<?php
namespace DrdPlus\Health\Afflictions\ElementalPertinence\EnumTypes;

use Doctrineum\String\StringEnumType;
use DrdPlus\Health\Afflictions\ElementalPertinence\AirPertinence;
use DrdPlus\Health\Afflictions\ElementalPertinence\EarthPertinence;
use DrdPlus\Health\Afflictions\ElementalPertinence\FirePertinence;
use DrdPlus\Health\Afflictions\ElementalPertinence\WaterPertinence;

class ElementalPertinenceType extends StringEnumType
{
    const ELEMENTAL_PERTINENCE = 'elemental_pertinence';

    public static function registerSelf()
    {
        parent::registerSelf();
        self::registerSubTypeEnum(FirePertinence::class, '~' . FirePertinence::FIRE . '$~');
        self::registerSubTypeEnum(WaterPertinence::class, '~' . WaterPertinence::WATER . '$~');
        self::registerSubTypeEnum(EarthPertinence::class, '~' . EarthPertinence::EARTH . '$~');
        self::registerSubTypeEnum(AirPertinence::class, '~' . AirPertinence::AIR . '$~');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::ELEMENTAL_PERTINENCE;
    }
}