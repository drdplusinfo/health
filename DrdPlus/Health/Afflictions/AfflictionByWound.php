<?php
namespace DrdPlus\Health\Afflictions;

use Doctrine\ORM\Mapping as ORM;
use DrdPlus\Health\Afflictions\Effects\AfflictionEffect;
use DrdPlus\Health\Afflictions\ElementalPertinence\ElementalPertinence;
use DrdPlus\Health\SeriousWound;

/**
 * @ORM\MappedSuperclass()
 */
abstract class AfflictionByWound extends Affliction
{
    /**
     * @var SeriousWound
     * @ORM\ManyToOne(targetEntity="\DrdPlus\Health\SeriousWound", cascade={"persist"})
     */
    private $seriousWound;

    /**
     * @param SeriousWound $seriousWound
     * @param AfflictionProperty $property
     * @param AfflictionDangerousness $dangerousness
     * @param AfflictionDomain $domain
     * @param AfflictionVirulence $virulence
     * @param AfflictionSource $source
     * @param AfflictionSize $size
     * @param ElementalPertinence $elementalPertinence
     * @param AfflictionEffect $effect
     * @param \DateInterval $outbreakPeriod
     * @param AfflictionName $afflictionName
     * @throws \DrdPlus\Health\Afflictions\Exceptions\WoundHasToBeFreshForAffliction
     * @throws \DrdPlus\Health\Exceptions\UnknownAfflictionOriginatingWound
     * @throws \DrdPlus\Health\Exceptions\AfflictionIsAlreadyRegistered
     */
    protected function __construct(
        SeriousWound $seriousWound, // wound can be healed, but never disappears - just stays healed
        AfflictionProperty $property,
        AfflictionDangerousness $dangerousness,
        AfflictionDomain $domain,
        AfflictionVirulence $virulence,
        AfflictionSource $source,
        AfflictionSize $size,
        ElementalPertinence $elementalPertinence,
        AfflictionEffect $effect,
        \DateInterval $outbreakPeriod,
        AfflictionName $afflictionName
    )
    {
        if ($seriousWound->isOld()) {
            throw new Exceptions\WoundHasToBeFreshForAffliction(
                "Given wound of value {$seriousWound} and origin '{$seriousWound->getWoundOrigin()}' should be untreated to create an affliction."
            );
        }
        $this->seriousWound = $seriousWound;
        parent::__construct(
            $seriousWound->getHealth(),
            $property,
            $dangerousness,
            $domain,
            $virulence,
            $source,
            $size,
            $elementalPertinence,
            $effect,
            $outbreakPeriod,
            $afflictionName
        );
    }

    /**
     * @return SeriousWound
     */
    public function getSeriousWound()
    {
        return $this->seriousWound;
    }

    /**
     * @return int
     */
    public function getWillMalus()
    {
        return 0; // currently no wound affliction can affect will
    }

    /**
     * @return int
     */
    public function getIntelligenceMalus()
    {
        return 0; // currently no wound affliction can affect will
    }

    /**
     * @return int
     */
    public function getCharismaMalus()
    {
        return 0; // currently no wound affliction can affect will
    }
}