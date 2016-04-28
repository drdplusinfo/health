<?php
namespace DrdPlus\Person\Health\EnumTypes;

use Doctrineum\Integer\IntegerEnumType;
use DrdPlus\Person\Health\TreatmentBoundary;

class TreatmentBoundaryType extends IntegerEnumType
{
    const TREATMENT_BOUNDARY = TreatmentBoundary::TREATMENT_BOUNDARY;
}