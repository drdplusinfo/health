<?php
namespace DrdPlus\Health\Afflictions\Effects;

/**
 * @method static HungerEffect getEnum($enumValue)
 */
class HungerEffect extends AfflictionEffect
{
    const HUNGER_EFFECT = 'hunger_effect';

    /**
     * @return HungerEffect
     */
    public static function getIt(): HungerEffect
    {
        return static::getEnum(self::HUNGER_EFFECT);
    }

    /**
     * @return bool
     */
    public function isEffectiveEvenOnSuccessAgainstTrap(): bool
    {
        return true;
    }

}