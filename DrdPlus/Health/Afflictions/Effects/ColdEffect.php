<?php
namespace DrdPlus\Health\Afflictions\Effects;

use DrdPlus\Health\Afflictions\SpecificAfflictions\Cold;
use DrdPlus\Tools\Calculations\SumAndRound;

class ColdEffect extends AfflictionEffect
{
    const COLD_EFFECT = 'cold_effect';

    /**
     * @return ColdEffect
     */
    public static function getIt()
    {
        return static::getEnum(self::COLD_EFFECT);
    }

    /**
     * {@inheritdoc}
     */
    public function isEffectiveEvenOnSuccessAgainstTrap()
    {
        return false;
    }

    /**
     * @param Cold $cold
     * @return int
     */
    public function getStrengthAdjustment(Cold $cold)
    {
        return -SumAndRound::ceil($cold->getSize()->getValue() / 4);
    }

    /**
     * @param Cold $cold
     * @return int
     */
    public function getAgilityAdjustment(Cold $cold)
    {
        return -SumAndRound::ceil($cold->getSize()->getValue() / 4);
    }

    /**
     * @param Cold $cold
     * @return int
     */
    public function getKnackAdjustment(Cold $cold)
    {
        return -SumAndRound::ceil($cold->getSize()->getValue() / 4);
    }
}