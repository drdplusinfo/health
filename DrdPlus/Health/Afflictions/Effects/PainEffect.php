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
    public static function getIt()
    {
        return static::getEnum(self::PAIN_EFFECT);
    }

    /**
     * {@inheritdoc}
     */
    public function isEffectiveEvenOnSuccessAgainstTrap()
    {
        return false;
    }

    /**
     * @param Pain $pain
     * @return int
     */
    public function getMalusFromPain(Pain $pain)
    {
        return -$pain->getSize()->getValue();
    }
}