<?php
namespace DrdPlus\Person\Health\Afflictions\Effects;

use DrdPlus\Person\Health\Afflictions\SpecificAfflictions\Bleeding;
use DrdPlus\Person\Health\Wound;
use DrdPlus\Person\Health\WoundOrigin;
use DrdPlus\Person\Health\WoundSize;
use DrdPlus\Tables\Measurements\Wounds\WoundsBonus;
use DrdPlus\Tables\Measurements\Wounds\WoundsTable;

class BleedingEffect extends AfflictionEffect
{
    const BLEEDING = 'bleeding';

    /**
     * @return BleedingEffect
     */
    public static function getIt()
    {
        return static::getEnum(self::BLEEDING);
    }

    /**
     * {@inheritdoc}
     */
    public function isEffectiveEvenOnSuccessAgainstTrap()
    {
        return true;
    }

    /**
     * @param Bleeding $bleeding
     * @param WoundsTable $woundsTable
     * @return Wound|false
     */
    public function getWound(Bleeding $bleeding, WoundsTable $woundsTable)
    {
        // see PPH page 78 right column, Bleeding
        $effectSize = $bleeding->getSize()->getValue() - 6;
        $woundsFromTable = $woundsTable->toWounds(new WoundsBonus($effectSize, $woundsTable));
        if ($woundsFromTable->getValue() < 1) {
            return false;
        }

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new Wound(
            $bleeding->getWound()->getHealth(),
            new WoundSize($woundsFromTable->getValue()),
            $bleeding->getWound()->getHealth()->getGridOfWounds()->isSeriousInjury($woundsFromTable->getValue())
                ? $bleeding->getWound()->getWoundOrigin()
                : WoundOrigin::getOrdinaryWoundOrigin()
        );
    }

}