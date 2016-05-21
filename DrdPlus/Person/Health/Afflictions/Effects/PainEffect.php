<?php
namespace DrdPlus\Person\Health\Afflictions\Effects;

use DrdPlus\Person\Health\Afflictions\SpecificAfflictions\Pain;

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