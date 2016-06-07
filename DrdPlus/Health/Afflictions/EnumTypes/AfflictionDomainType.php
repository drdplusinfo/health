<?php
namespace DrdPlus\Health\Afflictions\EnumTypes;

use Doctrineum\String\StringEnumType;

class AfflictionDomainType extends StringEnumType
{
    const AFFLICTION_DOMAIN = 'affliction_domain';

    /**
     * @return string
     */
    public function getName()
    {
        return self::AFFLICTION_DOMAIN;
    }
}