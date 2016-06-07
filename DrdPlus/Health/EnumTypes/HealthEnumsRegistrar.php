<?php
namespace DrdPlus\Health\EnumTypes;

use Doctrineum\DateInterval\DBAL\Types\DateIntervalType;
use DrdPlus\Health\Afflictions\Effects\EnumTypes\AfflictionEffectType;
use DrdPlus\Health\Afflictions\ElementalPertinence\EnumTypes\ElementalPertinenceType;
use DrdPlus\Health\Afflictions\EnumTypes\AfflictionDangerousnessType;
use DrdPlus\Health\Afflictions\EnumTypes\AfflictionDomainType;
use DrdPlus\Health\Afflictions\EnumTypes\AfflictionNameType;
use DrdPlus\Health\Afflictions\EnumTypes\AfflictionPropertyType;
use DrdPlus\Health\Afflictions\EnumTypes\AfflictionSizeType;
use DrdPlus\Health\Afflictions\EnumTypes\AfflictionSourceType;
use DrdPlus\Health\Afflictions\EnumTypes\AfflictionVirulenceType;

class HealthEnumsRegistrar
{
    public static function registerAll()
    {
        DateIntervalType::registerSelf();

        // Health
        TreatmentBoundaryType::registerSelf();
        WoundOriginType::registerSelf();
        MalusFromWoundsType::registerSelf();
        ReasonToRollAgainstMalusType::registerSelf();

        // Health\Afflictions
        AfflictionDangerousnessType::registerSelf();
        AfflictionDomainType::registerSelf();
        AfflictionNameType::registerSelf();
        AfflictionPropertyType::registerSelf();
        AfflictionSizeType::registerSelf();
        AfflictionSourceType::registerSelf();
        AfflictionVirulenceType::registerSelf();

        // Health\Afflictions\Effects
        AfflictionEffectType::registerSelf();

        // Health\Afflictions\Effects\ElementalPertinence
        ElementalPertinenceType::registerSelf();
    }
}