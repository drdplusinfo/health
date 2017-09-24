<?php
namespace DrdPlus\Health\Afflictions\Effects;

use DrdPlus\Health\Afflictions\SpecificAfflictions\Pain;

/**
 * @method static PainEffect getEnum($enumValue)
 */
class PainEffect extends AfflictionEffect
{
    const PAIN_EFFECT = 'pain_effect';

    /**
     * @return PainEffect
     */
    public static function getIt(): PainEffect
    {
        return static::getEnum(self::PAIN_EFFECT);
    }

    /**
     * @return bool
     */
    public function isEffectiveEvenOnSuccessAgainstTrap(): bool
    {
        return false;
    }

    /**
     * @param Pain $pain
     * @return int
     */
    public function getMalusFromPain(Pain $pain): int
    {
        return -$pain->getAfflictionSize()->getValue();
    }
}