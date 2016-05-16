<?php
namespace DrdPlus\Person\Health\Afflictions\Effects;

use DrdPlus\Person\Health\Afflictions\SpecificAfflictions\Pain;

class PainEffect extends AfflictionEffect
{
    const PAIN = 'pain';

    /**
     * @return PainEffect
     */
    public static function getIt()
    {
        return static::getEnum(self::PAIN);
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
    public function getMalusSize(Pain $pain)
    {
        return -$pain->getSize()->getValue();
    }
}