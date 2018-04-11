<?php
namespace DrdPlus\Health\EnumTypes;

use Doctrineum\DateInterval\DBAL\Types\DateIntervalType;
use DrdPlus\Codes\Body\EnumTypes\WoundOriginCodeType;
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
    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function registerAll(): void
    {
        WoundOriginCodeType::registerSelf();
        DateIntervalType::registerSelf();

        // Health
        TreatmentBoundaryType::registerSelf();
        MalusFromWoundsType::registerSelf();
        ReasonToRollAgainstWoundMalusType::registerSelf();

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