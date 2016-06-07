<?php
namespace DrdPlus\Health\Afflictions\EnumTypes;

use Doctrineum\Integer\IntegerEnumType;

class AfflictionSizeType extends IntegerEnumType
{
    const AFFLICTION_SIZE = 'affliction_size';

    /**
     * @return string
     */
    public function getName()
    {
        return self::AFFLICTION_SIZE;
    }
}