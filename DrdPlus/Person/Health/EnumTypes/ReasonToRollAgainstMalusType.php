<?php
namespace DrdPlus\Person\Health\EnumTypes;

use Doctrineum\String\StringEnumType;

class ReasonToRollAgainstMalusType extends StringEnumType
{
    const REASON_TO_ROLL_AGAINST_MALUS = 'reason_to_roll_against_malus';

    /**
     * @return string
     */
    public function getName()
    {
        return self::REASON_TO_ROLL_AGAINST_MALUS;
    }
}