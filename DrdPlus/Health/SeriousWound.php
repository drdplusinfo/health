<?php
namespace DrdPlus\Health;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 */
class SeriousWound extends Wound
{
    /**
     * @param Health $health
     * @param WoundSize $woundSize
     * @param SeriousWoundOrigin $seriousWoundOrigin
     * @throws \DrdPlus\Health\Exceptions\WoundHasToBeCreatedByHealthItself
     */
    public function __construct(Health $health, WoundSize $woundSize, SeriousWoundOrigin $seriousWoundOrigin)
    {
        parent::__construct($health, $woundSize, $seriousWoundOrigin);
    }

    /**
     * @return bool
     */
    public function isSerious(): bool
    {
        return true;
    }

}