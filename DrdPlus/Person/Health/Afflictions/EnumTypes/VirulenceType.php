<?php
namespace DrdPlus\Person\Health\Afflictions\EnumTypes;

use Doctrineum\String\StringEnumType;

class VirulenceType extends StringEnumType
{
    /**
     * should has the same value as @see \DrdPlus\Person\Health\Afflictions\Virulence::VIRULENCE
     * can not be linked with such constant to provide PhpStorm to/definition link
     */
    const VIRULENCE = 'virulence';

    /**
     * @return string
     */
    public function getName()
    {
        return self::VIRULENCE;
    }
}