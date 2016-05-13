<?php
namespace DrdPlus\Person\Health\Afflictions\Effects;

use DrdPlus\Person\Health\Afflictions\SpecificAfflictions\SeveredArm;

class SeveredArmEffect extends AfflictionEffect
{
    const SEVERED_ARM = 'severed_arm';

    /**
     * @return SeveredArmEffect
     */
    public static function getIt()
    {
        return static::getEnum(self::SEVERED_ARM);
    }

    /**
     * {@inheritdoc}
     */
    public function isEffectiveEvenOnSuccessAgainst()
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