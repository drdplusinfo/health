<?php
namespace DrdPlus\Person\Health\Afflictions\EnumTypes;

use Doctrineum\String\StringEnumType;

class AfflictionVirulenceType extends StringEnumType
{
    /**
     * should has the same value as @see \DrdPlus\Person\Health\Afflictions\AfflictionVirulence::AFFLICTION_VIRULENCE
     * can not be linked with such constant to provide PhpStorm to/definition link
     */
    const AFFLICTION_VIRULENCE = 'affliction_virulence';

    /**
     * @return string
     */
    public function getName()
    {
        return self::AFFLICTION_VIRULENCE;
    }
}