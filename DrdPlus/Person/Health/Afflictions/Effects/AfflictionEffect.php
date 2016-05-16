<?php
namespace DrdPlus\Person\Health\Afflictions\Effects;

use Doctrineum\Scalar\ScalarEnum;

abstract class AfflictionEffect extends ScalarEnum
{
    /**
     * Even if affected person success on roll against trap, comes this effect into play.
     * @return bool
     */
    abstract public function isEffectiveEvenOnSuccessAgainstTrap();
}