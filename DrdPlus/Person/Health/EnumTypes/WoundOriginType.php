<?php
namespace DrdPlus\Person\Health\EnumTypes;

use Doctrineum\String\StringEnumType;
use DrdPlus\Person\Health\WoundOrigin;

class WoundOriginType extends StringEnumType
{
    const WOUND_ORIGIN = WoundOrigin::WOUND_ORIGIN;
}