<?php
namespace DrdPlus\Person\Health\EnumTypes;

use Doctrineum\Integer\IntegerEnumType;

class TreatmentBoundaryType extends IntegerEnumType
{
    const TREATMENT_BOUNDARY = 'treatment_boundary';

    /**
     * @return string
     */
    public function getName()
    {
        return self::TREATMENT_BOUNDARY;
    }
}