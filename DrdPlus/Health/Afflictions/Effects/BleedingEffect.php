<?php
namespace DrdPlus\Health\Afflictions\Effects;

use DrdPlus\Health\Afflictions\SpecificAfflictions\Bleeding;
use DrdPlus\Health\OrdinaryWound;
use DrdPlus\Health\SeriousWound;
use DrdPlus\Health\WoundSize;
use DrdPlus\Properties\Derived\WoundBoundary;
use DrdPlus\Tables\Measurements\Wounds\WoundsBonus;
use DrdPlus\Tables\Measurements\Wounds\WoundsTable;

/**
 * @method static BleedingEffect getEnum($enumValue)
 */
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
     * @param WoundBoundary $woundBoundary
     * @return SeriousWound|OrdinaryWound|false
     * @throws \DrdPlus\Health\Exceptions\NeedsToRollAgainstMalusFirst
     */
    public function bleed(Bleeding $bleeding, WoundsTable $woundsTable, WoundBoundary $woundBoundary)
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
            $woundBoundary
        );
    }

}