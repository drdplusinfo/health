<?php
namespace DrdPlus\Health\Afflictions\SpecificAfflictions;

use DrdPlus\Codes\PropertyCodes;
use DrdPlus\Health\Afflictions\AfflictionByWound;
use DrdPlus\Health\Afflictions\AfflictionDangerousness;
use DrdPlus\Health\Afflictions\AfflictionDomain;
use DrdPlus\Health\Afflictions\AfflictionName;
use DrdPlus\Health\Afflictions\AfflictionProperty;
use DrdPlus\Health\Afflictions\AfflictionSize;
use DrdPlus\Health\Afflictions\AfflictionSource;
use DrdPlus\Health\Afflictions\AfflictionVirulence;
use DrdPlus\Health\Afflictions\Effects\ColdEffect;
use DrdPlus\Health\Afflictions\ElementalPertinence\WaterPertinence;
use DrdPlus\Health\SeriousWound;
use Doctrine\ORM\Mapping as ORM;

/**
 * See PPH page 78, left column
 * @ORM\Entity
 */
class Cold extends AfflictionByWound
{
    const COLD = 'cold';

    /**
     * @param SeriousWound $seriousWound
     * @return Cold
     * @throws \DrdPlus\Health\Afflictions\Exceptions\WoundHasToBeFreshForAffliction
     */
    public static function createIt(SeriousWound $seriousWound)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return new static(
            $seriousWound,
            AfflictionDomain::getPhysicalAffliction(),
            AfflictionVirulence::getDayVirulence(),
            AfflictionSource::getActiveSource(),
            AfflictionProperty::getIt(PropertyCodes::TOUGHNESS),
            AfflictionDangerousness::getIt(7),
            AfflictionSize::getIt(4),
            WaterPertinence::getPlus(),
            ColdEffect::getIt(),
            new \DateInterval('P1D'),
            AfflictionName::getIt(self::COLD)
        );
    }
}