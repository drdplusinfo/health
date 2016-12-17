<?php
namespace DrdPlus\Health\Afflictions\Effects;

/**
 * @method static ThirstEffect getEnum($enumValue)
 */
class ThirstEffect extends AfflictionEffect
{
    const THIRST_EFFECT = 'thirst_effect';

    /**
     * @return ThirstEffect
     */
    public static function getIt()
    {
        return static::getEnum(self::THIRST_EFFECT);
    }

    /**
     * @return bool
     */
    public function isEffectiveEvenOnSuccessAgainstTrap()
    {
        return true;
    }

}