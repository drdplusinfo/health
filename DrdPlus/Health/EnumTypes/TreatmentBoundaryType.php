<?php
namespace DrdPlus\Health\EnumTypes;

use Doctrineum\Integer\IntegerEnumType;

class TreatmentBoundaryType extends IntegerEnumType
{
    const TREATMENT_BOUNDARY = 'treatment_boundary';

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::TREATMENT_BOUNDARY;
    }
}