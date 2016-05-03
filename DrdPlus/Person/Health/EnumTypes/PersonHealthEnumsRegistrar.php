<?php
namespace DrdPlus\Person\Health\EnumTypes;

use DrdPlus\Person\Health\Affliction\EnumTypes\AfflictionDomainType;
use DrdPlus\Person\Health\Affliction\EnumTypes\VirulenceType;

class PersonHealthEnumsRegistrar
{
    public static function registerAll()
    {
        TreatmentBoundaryType::registerSelf();
        WoundOriginType::registerSelf();
        AfflictionDomainType::registerSelf();
        VirulenceType::registerSelf();
    }
}