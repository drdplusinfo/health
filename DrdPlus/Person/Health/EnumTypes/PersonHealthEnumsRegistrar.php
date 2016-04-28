<?php
namespace DrdPlus\Person\Health\EnumTypes;

class PersonHealthEnumsRegistrar
{
    public static function registerAll()
    {
        TreatmentBoundaryType::registerSelf();
        WoundOriginType::registerSelf();
    }
}