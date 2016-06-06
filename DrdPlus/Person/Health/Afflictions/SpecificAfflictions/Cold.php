<?php
namespace DrdPlus\Person\Health\Afflictions\SpecificAfflictions;

use DrdPlus\Codes\PropertyCodes;
use DrdPlus\Person\Health\Afflictions\AfflictionByWound;
use DrdPlus\Person\Health\Afflictions\AfflictionDangerousness;
use DrdPlus\Person\Health\Afflictions\AfflictionDomain;
use DrdPlus\Person\Health\Afflictions\AfflictionName;
use DrdPlus\Person\Health\Afflictions\AfflictionProperty;
use DrdPlus\Person\Health\Afflictions\AfflictionSize;
use DrdPlus\Person\Health\Afflictions\AfflictionSource;
use DrdPlus\Person\Health\Afflictions\AfflictionVirulence;
use DrdPlus\Person\Health\Afflictions\Effects\ColdEffect;
use DrdPlus\Person\Health\Afflictions\ElementalPertinence\WaterPertinence;
use DrdPlus\Person\Health\SeriousWound;
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
     * @throws \DrdPlus\Person\Health\Afflictions\Exceptions\WoundHasToBeFreshForAffliction
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