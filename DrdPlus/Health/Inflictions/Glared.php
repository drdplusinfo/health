<?php
namespace DrdPlus\Health\Inflictions;

use Doctrineum\Entity\Entity;
use DrdPlus\Health\Health;
use DrdPlus\Lighting\Glare;
use DrdPlus\Tables\Measurements\Time\Time;
use DrdPlus\Calculations\SumAndRound;
use Granam\Strict\Object\StrictObject;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Glared extends StrictObject implements Entity
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
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
     * @var int
     * @ORM\Column(type="integer")
     */
    private $gettingUsedToForRounds;
    /**
     * @var Health
     * @ORM\OneToOne(targetEntity="\DrdPlus\Health\Health", mappedBy="glared")
     */
    private $health;

    /**
     * @param Health $health
     * @return Glared
     */
    public static function createWithoutGlare(Health $health)
    {
        return new static(0, 0, $health);
    }

    /**
     * @param Glare $glare
     * @param Health $health
     * @return Glared
     */
    public static function createFromGlare(Glare $glare, Health $health)
    {
        return new static($glare->getMalus(), $glare->isShined(), $health);
    }

    /**
     * @param int $malus
     * @param bool $isShined
     * @param Health $health
     */
    private function __construct($malus, $isShined, Health $health)
    {
        $this->malus = $malus;
        $this->shined = $isShined;
        $this->gettingUsedToForRounds = 0;
        $this->health = $health;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gives malus to activities requiring sight, already lowered by a time getting used to the glare, if any.
     *
     * @return int negative integer or zero
     */
    public function getCurrentMalus()
    {
        if ($this->getGettingUsedToForRounds() === 0) {
            return $this->malus;
        }
        if ($this->isShined()) {
            // each rounds of getting used to lowers malus by one point
            return $this->malus + $this->getGettingUsedToForRounds();
        }

        // ten rounds of getting used to are needed to lower glare malus by a single point
        return $this->malus + SumAndRound::floor($this->getGettingUsedToForRounds() / 10);
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

    /**
     * @return Health
     */
    public function getHealth()
    {
        return $this->health;
    }

    /**
     * Total rounds of getting used to current contrast, which lowers glare and malus.
     *
     * @param Time $gettingUsedToFor
     */
    public function setGettingUsedToForTime(Time $gettingUsedToFor)
    {
        $inRounds = $gettingUsedToFor->findRounds();
        if ($inRounds === null) {
            // it can not be expressed by rounds, so definitely get used to it - malus zeroed
            if ($this->isShined()) {
                $this->gettingUsedToForRounds = -$this->malus;
            } else { // if blinded than needs ten more time to get used to it
                $this->gettingUsedToForRounds = -$this->malus * 10;
            }

            return;
        }
        if ($this->isShined()) {
            if ($this->malus + $inRounds->getValue() > 0) { // more time than needed, malus is gone
                $this->gettingUsedToForRounds = -$this->malus; // zeroed malus in fact
            } else {
                $this->gettingUsedToForRounds = $inRounds->getValue(); // not enough to remove whole glare and malus

            }
        } else { // if blinded than needs ten more time to get used to it
            if ($this->malus + $inRounds->getValue() / 10 > 0) { // more time than needed, malus is gone
                $this->gettingUsedToForRounds = -$this->malus * 10; // zeroed malus in fact
            } else {
                $this->gettingUsedToForRounds = $inRounds->getValue(); // not enough to remove whole glare and malus
            }
        }
    }

    /**
     * Gives number of rounds when getting used to current contrast.
     *
     * @return int
     */
    public function getGettingUsedToForRounds()
    {
        return $this->gettingUsedToForRounds;
    }
}