<?php
namespace DrdPlus\Person\Health\EnumTypes;

use Doctrineum\Integer\IntegerEnumType;

class MalusFromWoundsType extends IntegerEnumType
{
    const MALUS_FROM_WOUNDS = 'malus_from_wounds';

    /**
     * @return string
     */
    public function getName()
    {
        return self::MALUS_FROM_WOUNDS;
    }
}