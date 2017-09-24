<?php
namespace DrdPlus\Health\Afflictions\EnumTypes;

use Doctrineum\String\StringEnumType;

class AfflictionNameType extends StringEnumType
{
    const AFFLICTION_NAME = 'affliction_name';

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::AFFLICTION_NAME;
    }
}