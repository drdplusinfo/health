<?php
namespace DrdPlus\Health\EnumTypes;

use Doctrineum\String\StringEnumType;

class ReasonToRollAgainstWoundMalusType extends StringEnumType
{
    const REASON_TO_ROLL_AGAINST_WOUND_MALUS = 'reason_to_roll_against_wound_malus';

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::REASON_TO_ROLL_AGAINST_WOUND_MALUS;
    }
}