<?php
namespace DrdPlus\Person\Health\EnumTypes;

use Doctrineum\DateInterval\DBAL\Types\DateIntervalType;
use DrdPlus\Person\Health\Afflictions\Effects\EnumType\AfflictionEffectType;
use DrdPlus\Person\Health\Afflictions\ElementalPertinence\EnumTypes\ElementalPertinenceType;
use DrdPlus\Person\Health\Afflictions\EnumTypes\AfflictionDangerousnessType;
use DrdPlus\Person\Health\Afflictions\EnumTypes\AfflictionDomainType;
use DrdPlus\Person\Health\Afflictions\EnumTypes\AfflictionNameType;
use DrdPlus\Person\Health\Afflictions\EnumTypes\AfflictionPropertyType;
use DrdPlus\Person\Health\Afflictions\EnumTypes\SourceEnumType;
use DrdPlus\Person\Health\Afflictions\EnumTypes\VirulenceType;

class PersonHealthEnumsRegistrar
{
    public static function registerAll()
    {
        TreatmentBoundaryType::registerSelf();
        WoundOriginType::registerSelf();

        AfflictionDomainType::registerSelf();
        VirulenceType::registerSelf();
        SourceEnumType::registerSelf();
        ElementalPertinenceType::registerAllPertinence();
        AfflictionPropertyType::registerSelf();
        DateIntervalType::registerSelf();
        AfflictionDangerousnessType::registerSelf();
        AfflictionEffectType::registerAllEffects();
        AfflictionNameType::registerSelf();
    }
}