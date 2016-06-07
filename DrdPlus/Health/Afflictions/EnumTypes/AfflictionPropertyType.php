<?php
namespace DrdPlus\Health\Afflictions\EnumTypes;

use Doctrineum\String\StringEnumType;

class AfflictionPropertyType extends StringEnumType
{
    const AFFLICTION_PROPERTY = 'affliction_property';

    /**
     * @return string
     */
    public function getName()
    {
        return self::AFFLICTION_PROPERTY;
    }
}