<?php
namespace DrdPlus\Health;

use Doctrineum\Entity\Entity;
use DrdPlus\RollsOn\Traps\RollOnSenses;
use Granam\Strict\Object\StrictObject;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Glare extends StrictObject implements Entity
{
    /**
     * @var int
     * @ORM\Id @ORM\GeneratedValue(strategy="AUTO") @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $malus;
    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $shined;

    /**
     * @param Contrast $contrast
     * @param RollOnSenses $rollOnSenses
     */
    public function __construct(Contrast $contrast, RollOnSenses $rollOnSenses)
    {
        if ($contrast->getValue() <= $rollOnSenses->getValue()) {
            $possibleMalus = -($contrast->getValue() - 1);
        } else {
            $possibleMalus = -($contrast->getValue() - 7);
        }
        $this->malus = 0;
        if ($possibleMalus < 0) {
            $this->malus = $possibleMalus;
        }
        $this->shined = $contrast->isFromDarkToLight(); // otherwise blinded
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getMalus()
    {
        return $this->malus;
    }

    /**
     * @return bool
     */
    public function isShined()
    {
        return $this->shined;
    }

    /**
     * @return bool
     */
    public function isBlinded()
    {
        return !$this->isShined();
    }
}