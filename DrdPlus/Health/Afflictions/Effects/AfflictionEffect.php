<?php
namespace DrdPlus\Health\Afflictions\Effects;

use Doctrineum\Scalar\ScalarEnum;

abstract class AfflictionEffect extends ScalarEnum
{
    /**
     * Even if affected creature success on roll against trap, comes this effect into play.
     * @return bool
     */
    abstract public function isEffectiveEvenOnSuccessAgainstTrap();
}