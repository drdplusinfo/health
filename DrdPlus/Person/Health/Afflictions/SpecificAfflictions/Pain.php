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
use DrdPlus\Person\Health\Afflictions\Effects\PainEffect;
use DrdPlus\Person\Health\Afflictions\ElementalPertinence\ElementalPertinence;
use DrdPlus\Person\Health\SeriousWound;

/**
 * see PPH page 79 left column
 * @method PainEffect getEffect
 */
class Pain extends AfflictionByWound
{
    const PAIN = 'pain';

    /**
     * @param SeriousWound $seriousWound
     * @param AfflictionVirulence $virulence
     * @param AfflictionSize $painSize
     * @param ElementalPertinence $elementalPertinence
     * @return Pain
     * @throws \DrdPlus\Person\Health\Afflictions\Exceptions\WoundHasToBeFreshForAffliction
     */
    public static function createIt(
        SeriousWound $seriousWound,
        AfflictionVirulence $virulence,
        AfflictionSize $painSize,
        ElementalPertinence $elementalPertinence
    )
    {
        return new static(
            $seriousWound,
            AfflictionDomain::getPhysicalAffliction(),
            $virulence,
            AfflictionSource::getExternalSource(),
            AfflictionProperty::getIt(PropertyCodes::WILL),
            AfflictionDangerousness::getIt(10 + $painSize->getValue()),
            $painSize,
            $elementalPertinence,
            PainEffect::getIt(),
            new \DateInterval('PT0S'), // immediately
            AfflictionName::getIt(self::PAIN)
        );
    }

    /**
     * @return int
     */
    public function getMalus()
    {
        return $this->getEffect()->getMalusFromPain($this);
    }
}