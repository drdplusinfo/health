<?php
namespace DrdPlus\Person\Health\Affliction\EnumTypes;

use Doctrineum\String\StringEnumType;
use DrdPlus\Person\Health\Affliction\Virulence;

class VirulenceType extends StringEnumType
{
    const VIRULENCE = Virulence::VIRULENCE;
}