<?php
namespace DrdPlus\Person\Health\EnumTypes;

use Doctrineum\DateInterval\DBAL\Types\DateIntervalType;
use DrdPlus\Person\Health\Afflictions\Effects\EnumTypes\AfflictionEffectType;
use DrdPlus\Person\Health\Afflictions\ElementalPertinence\EnumTypes\ElementalPertinenceType;
use DrdPlus\Person\Health\Afflictions\EnumTypes\AfflictionDangerousnessType;
use DrdPlus\Person\Health\Afflictions\EnumTypes\AfflictionDomainType;
use DrdPlus\Person\Health\Afflictions\EnumTypes\AfflictionNameType;
use DrdPlus\Person\Health\Afflictions\EnumTypes\AfflictionPropertyType;
use DrdPlus\Person\Health\Afflictions\EnumTypes\AfflictionSizeType;
use DrdPlus\Person\Health\Afflictions\EnumTypes\AfflictionSourceType;
use DrdPlus\Person\Health\Afflictions\EnumTypes\AfflictionVirulenceType;

class PersonHealthEnumsRegistrar
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