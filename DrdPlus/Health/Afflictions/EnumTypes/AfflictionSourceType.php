<?php
namespace DrdPlus\Health\Afflictions\EnumTypes;

use Doctrineum\String\StringEnumType;

class AfflictionSourceType extends StringEnumType
{
    /**
     * should has the same value as @see \DrdPlus\Health\Afflictions\AfflictionSource::AFFLICTION_SOURCE
     * can not be linked with such constant to provide PhpStorm to/definition link
     */
    const AFFLICTION_SOURCE = 'affliction_source';

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::AFFLICTION_SOURCE;
    }
}