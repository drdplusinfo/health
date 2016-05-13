<?php
namespace DrdPlus\Person\Health\EnumTypes;

use Doctrineum\String\StringEnumType;

class WoundOriginType extends StringEnumType
{
    const WOUND_ORIGIN = 'wound_origin';

    /**
     * @return string
     */
    public function getName()
    {
        return self::WOUND_ORIGIN;
    }

}