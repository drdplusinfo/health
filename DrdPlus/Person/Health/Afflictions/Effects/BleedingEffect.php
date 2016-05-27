<?php
namespace DrdPlus\Person\Health\Afflictions\Effects;

use Drd\DiceRoll\Templates\Rollers\Roller2d6DrdPlus;
use DrdPlus\Person\Health\Afflictions\SpecificAfflictions\Bleeding;
use DrdPlus\Person\Health\OrdinaryWound;
use DrdPlus\Person\Health\SeriousWound;
use DrdPlus\Person\Health\WoundSize;
use DrdPlus\Properties\Base\Will;
use DrdPlus\Tables\Measurements\Wounds\WoundsBonus;
use DrdPlus\Tables\Measurements\Wounds\WoundsTable;

class BleedingEffect extends AfflictionEffect
{
    const BLEEDING_EFFECT = 'bleeding_effect';

    /**
     * @return BleedingEffect
     */
    public static function getIt()
    {
        return static::getEnum(self::BLEEDING_EFFECT);
    }

    /**
     * {@inheritdoc}
     */
    public function isEffectiveEvenOnSuccessAgainstTrap()
    {
        return true;
    }

    /**
     * Creates new wound right in the health of origin wound
     * @param Bleeding $bleeding
     * @param WoundsTable $woundsTable
     * @param Will $will
     * @param Roller2d6DrdPlus $roller2d6DrdPlus
     * @return SeriousWound|OrdinaryWound|false
     */
    public function bleed(
        Bleeding $bleeding,
        WoundsTable $woundsTable,
        Will $will,
        Roller2d6DrdPlus $roller2d6DrdPlus
    )
    {
        // see PPH page 78 right column, Bleeding
        $effectSize = $bleeding->getSize()->getValue() - 6;
        $woundsFromTable = $woundsTable->toWounds(new WoundsBonus($effectSize, $woundsTable));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $woundSize = new WoundSize($woundsFromTable->getValue());
        $woundCausedBleeding = $bleeding->getSeriousWound();

        return $woundCausedBleeding->getHealth()->createWound(
            $woundSize,
            $woundCausedBleeding->getWoundOrigin(),
            $will,
            $roller2d6DrdPlus
        );
    }

}