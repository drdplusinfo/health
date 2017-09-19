<?php
namespace DrdPlus\Health\Afflictions\Effects;

use DrdPlus\Health\Afflictions\SpecificAfflictions\SeveredArm;

/**
 * @method static SeveredArmEffect getEnum($enumValue)
 */
class SeveredArmEffect extends AfflictionEffect
{
    const SEVERED_ARM_EFFECT = 'severed_arm_effect';

    /**
     * @return SeveredArmEffect
     */
    public static function getIt(): SeveredArmEffect
    {
        return static::getEnum(self::SEVERED_ARM_EFFECT);
    }

    public function isEffectiveEvenOnSuccessAgainstTrap(): bool
    {
        return true;
    }

    public function getStrengthMalus(SeveredArm $severedArm): int
    {
        return -$severedArm->getSize()->getValue();
    }

    public function getKnackMalus(SeveredArm $severedArm): int
    {
        return -2 * $severedArm->getSize()->getValue();
    }

}