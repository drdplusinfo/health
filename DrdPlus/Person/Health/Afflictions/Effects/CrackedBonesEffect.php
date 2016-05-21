<?php
namespace DrdPlus\Person\Health\Afflictions\Effects;

use DrdPlus\Person\Health\Afflictions\SpecificAfflictions\CrackedBones;

class CrackedBonesEffect extends AfflictionEffect
{
    const CRACKED_BONES_EFFECT = 'cracked_bones_effect';

    /**
     * @return CrackedBonesEffect
     */
    public static function getIt()
    {
        return static::getEnum(self::CRACKED_BONES_EFFECT);
    }

    /**
     * {@inheritdoc}
     */
    public function isEffectiveEvenOnSuccessAgainstTrap()
    {
        return true;
    }

    /**
     * @param CrackedBones $crackedBones
     * @return int
     */
    public function getHealingMalus(CrackedBones $crackedBones)
    {
        // note: affliction size is always at least zero
        return -$crackedBones->getSize()->getValue();
    }

}