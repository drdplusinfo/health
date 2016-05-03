<?php
namespace DrdPlus\Person\Health\Affliction;

use Doctrine\ORM\Mapping as ORM;
use Doctrineum\Entity\Entity;
use DrdPlus\Person\Health\Health;
use Granam\Integer\Tools\ToInteger;
use Granam\Scalar\Tools\ToString;
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
     * @var Health
     * @ORM\ManyToOne(targetEntity="Health", cascade={"persist"}, inversedBy="affliction")
     */
    private $health;
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
     * @var Source
     */
    private $source;
    /**
     * @var string
     */
    private $propertyCode;
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

    /**
     * @param Health $health
     * @param AfflictionDomain $domain
     * @param Virulence $virulence
     * @param Source $source
     * @param string $propertyCode
     * @param int $dangerousness
     * @param $size
     * @param $elementalPertinence
     * @param $effect
     * @param $outbreakPeriod
     */
    public function __construct(
        Health $health,
        AfflictionDomain $domain,
        Virulence $virulence,
        Source $source,
        $propertyCode,
        $dangerousness,
        $size,
        $elementalPertinence,
        $effect,
        $outbreakPeriod
    )
    {
        $this->health = $health;
        $this->domain = $domain;
        $this->virulence = $virulence;
        $this->source = $source;
        $this->propertyCode = ToString::toString($propertyCode);
        $this->dangerousness = ToInteger::toInteger($dangerousness);
        $this->size = $size; // TODO property type
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
     * @return Health
     */
    public function getHealth()
    {
        return $this->health;
    }

}