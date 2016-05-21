<?php
namespace DrdPlus\Person\Health\Afflictions\Effects;

use DrdPlus\Person\Health\Afflictions\SpecificAfflictions\SeveredArm;

class SeveredArmEffect extends AfflictionEffect
{
    const SEVERED_ARM_EFFECT = 'severed_arm_effect';

    /**
     * @return SeveredArmEffect
     */
    public static function getIt()
    {
        return static::getEnum(self::SEVERED_ARM_EFFECT);
    }

    /**
     * {@inheritdoc}
     */
    public function isEffectiveEvenOnSuccessAgainstTrap()
    {
        return true;
    }

    /**
     * @param SeveredArm $severedArm
     * @return int
     */
    public function getStrengthAdjustment(SeveredArm $severedArm)
    {
        return -$severedArm->getSize()->getValue();
    }

    /**
     * @param SeveredArm $severedArm
     * @return int
     */
    public function getKnackAdjustment(SeveredArm $severedArm)
    {
        return -2 * $severedArm->getSize()->getValue();
    }

}