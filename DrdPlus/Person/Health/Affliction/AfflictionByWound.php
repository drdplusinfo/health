<?php
namespace DrdPlus\Person\Health\Affliction;

use Doctrine\ORM\Mapping as ORM;
use Doctrineum\Entity\Entity;
use DrdPlus\Properties\Property;
use Granam\Strict\Object\StrictObject;

class AfflictionByWound extends StrictObject implements Entity
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @var AfflictionDomain
     * @ORM\Column(type="affliction_domain")
     */
    private $domain;
    /**
     * @var Virulence
     * @ORM\Column(type="virulence")
     */
    private $virulence;
    /**
     * @var
     */
    private $source;
    /**
     * @var Property
     */
    private $property;
    /**
     * @var
     */
    private $dangerousness;
    /**
     * @var
     */
    private $size;
    /**
     * @var
     */
    private $elementalPertinence;
    /**
     * @var
     */
    private $effect;
    /**
     * @var
     */
    private $outbreakPeriod;

    public function __construct(
        AfflictionDomain $domain,
        Virulence $virulence,
        $source,
        Property $property,
        $dangerousness,
        $size,
        $elementalPertinence,
        $effect,
        $outbreakPeriod
    )
    {
        $this->domain = $domain;
        $this->virulence = $virulence;
        $this->source = $source;
        $this->property = $property;
        $this->dangerousness = $dangerousness;
        $this->size = $size;
        $this->elementalPertinence = $elementalPertinence;
        $this->effect = $effect;
        $this->outbreakPeriod = $outbreakPeriod;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Virulence
     */
    public function getVirulence()
    {
        return $this->virulence;
    }
}