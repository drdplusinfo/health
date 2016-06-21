<?php
namespace DrdPlus\Health\Afflictions\SpecificAfflictions;

use DrdPlus\Codes\PropertyCode;
use DrdPlus\Health\Afflictions\AfflictionByWound;
use DrdPlus\Health\Afflictions\AfflictionDangerousness;
use DrdPlus\Health\Afflictions\AfflictionDomain;
use DrdPlus\Health\Afflictions\AfflictionName;
use DrdPlus\Health\Afflictions\AfflictionProperty;
use DrdPlus\Health\Afflictions\AfflictionSize;
use DrdPlus\Health\Afflictions\AfflictionSource;
use DrdPlus\Health\Afflictions\AfflictionVirulence;
use DrdPlus\Health\Afflictions\Effects\PainEffect;
use DrdPlus\Health\Afflictions\ElementalPertinence\ElementalPertinence;
use DrdPlus\Health\SeriousWound;
use Doctrine\ORM\Mapping as ORM;

/**
 * see PPH page 79 left column
 * @method PainEffect getEffect
 * 
 * @ORM\Entity
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
     * @throws \DrdPlus\Health\Afflictions\Exceptions\WoundHasToBeFreshForAffliction
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
            AfflictionProperty::getIt(PropertyCode::WILL),
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