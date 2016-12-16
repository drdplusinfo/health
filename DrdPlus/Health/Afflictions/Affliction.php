<?php
namespace DrdPlus\Health\Afflictions;

use Doctrine\ORM\Mapping as ORM;
use Doctrineum\Entity\Entity;
use DrdPlus\Health\Afflictions\Effects\AfflictionEffect;
use DrdPlus\Health\Afflictions\ElementalPertinence\ElementalPertinence;
use DrdPlus\Health\Health;
use Granam\Strict\Object\StrictObject;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "bleeding" = "\DrdPlus\Health\Afflictions\SpecificAfflictions\Bleeding",
 *     "severed_arm" = "\DrdPlus\Health\Afflictions\SpecificAfflictions\SeveredArm",
 *     "cold" = "\DrdPlus\Health\Afflictions\SpecificAfflictions\Cold",
 *     "cracked_bones" = "\DrdPlus\Health\Afflictions\SpecificAfflictions\CrackedBones",
 *     "pain" = "\DrdPlus\Health\Afflictions\SpecificAfflictions\Pain",
 *     "hunger" = "\DrdPlus\Health\Afflictions\SpecificAfflictions\Hunger",
 * })
 */
abstract class Affliction extends StrictObject implements Entity
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @var \DrdPlus\Health\Health
     * @ORM\ManyToOne(targetEntity="\DrdPlus\Health\Health", cascade={"persist"}, inversedBy="afflictions")
     */
    private $health;
    /**
     * @var AfflictionProperty
     * @ORM\Column(type="affliction_property")
     */
    private $property;
    /**
     * @var AfflictionDangerousness
     * @ORM\Column(type="affliction_dangerousness")
     */
    private $dangerousness;
    /**
     * @var AfflictionDomain
     * @ORM\Column(type="affliction_domain")
     */
    private $domain;
    /**
     * @var AfflictionVirulence
     * @ORM\Column(type="affliction_virulence")
     */
    private $virulence;
    /**
     * @var AfflictionSource
     * @ORM\Column(type="affliction_source")
     */
    private $source;
    /**
     * @var AfflictionSize
     * @ORM\Column(type="affliction_size")
     */
    private $size;
    /**
     * @var ElementalPertinence
     * @ORM\Column(type="elemental_pertinence")
     */
    private $elementalPertinence;
    /**
     * @var AfflictionEffect
     * @ORM\Column(type="affliction_effect")
     */
    private $afflictionEffect;
    /**
     * @var \DateInterval
     * @ORM\Column(type="date_interval")
     */
    private $outbreakPeriod;
    /**
     * @var AfflictionName
     * @ORM\Column(type="affliction_name")
     */
    private $afflictionName;

    /**
     * @param Health $health
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
     * @throws \DrdPlus\Health\Exceptions\UnknownAfflictionOriginatingWound
     * @throws \DrdPlus\Health\Exceptions\AfflictionIsAlreadyRegistered
     */
    protected function __construct(
        Health $health,
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
        $health->addAffliction($this);
        $this->property = $property;
        $this->dangerousness = $dangerousness;
        /** @noinspection UnusedConstructorDependenciesInspection */
        $this->health = $health;
        $this->domain = $domain;
        $this->virulence = $virulence;
        $this->source = $source;
        $this->size = $size;
        $this->elementalPertinence = $elementalPertinence;
        $this->afflictionEffect = $effect;
        $this->outbreakPeriod = $outbreakPeriod;
        $this->afflictionName = $afflictionName;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return AfflictionProperty
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @return AfflictionDangerousness
     */
    public function getDangerousness()
    {
        return $this->dangerousness;
    }

    /**
     * @return AfflictionDomain
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return AfflictionVirulence
     */
    public function getVirulence()
    {
        return $this->virulence;
    }

    /**
     * @return AfflictionSource
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return AfflictionSize
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return ElementalPertinence
     */
    public function getElementalPertinence()
    {
        return $this->elementalPertinence;
    }

    /**
     * @return AfflictionEffect
     */
    public function getAfflictionEffect()
    {
        return $this->afflictionEffect;
    }

    /**
     * @return \DateInterval
     */
    public function getOutbreakPeriod()
    {
        return $this->outbreakPeriod;
    }

    /**
     * @return AfflictionName
     */
    public function getName()
    {
        return $this->afflictionName;
    }

    /**
     * @return int
     */
    abstract public function getHealMalus();

    /**
     * @return int
     */
    abstract public function getMalusToActivities();

    /**
     * @return int
     */
    abstract public function getStrengthMalus();

    /**
     * @return int
     */
    abstract public function getAgilityMalus();

    /**
     * @return int
     */
    abstract public function getKnackMalus();

    /**
     * @return int
     */
    public function getWillMalus()
    {
        return 0; // currently no affliction can affect will
    }

    /**
     * @return int
     */
    public function getIntelligenceMalus()
    {
        return 0; // currently no affliction can affect intelligence
    }

    /**
     * @return int
     */
    public function getCharismaMalus()
    {
        return 0; // currently no affliction can affect charisma
    }
}