<?php
namespace DrdPlus\Person\Health\Afflictions\EnumTypes;

use Doctrineum\String\StringEnumType;

class AfflictionSourceType extends StringEnumType
{
    /**
     * should has the same value as @see \DrdPlus\Person\Health\Afflictions\Source::SOURCE
     * can not be linked with such constant to provide PhpStorm to/definition link
     */
    const SOURCE = 'source';

    /**
     * @return string
     */
    public function getName()
    {
        return self::SOURCE;
    }
}