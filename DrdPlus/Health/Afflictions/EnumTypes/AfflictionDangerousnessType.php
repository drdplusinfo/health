<?php
namespace DrdPlus\Health\Afflictions\EnumTypes;

use Doctrineum\Integer\IntegerEnumType;

class AfflictionDangerousnessType extends IntegerEnumType
{
    const AFFLICTION_DANGEROUSNESS = 'affliction_dangerousness';

    /**
     * @return string
     */
    public function getName()
    {
        return self::AFFLICTION_DANGEROUSNESS;
    }
}