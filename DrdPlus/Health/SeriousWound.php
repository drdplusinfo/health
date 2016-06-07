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
     * @param SpecificWoundOrigin $specificWoundOrigin
     * @throws \DrdPlus\Health\Exceptions\WoundHasToBeCreatedByHealthItself
     */
    public function __construct(Health $health, WoundSize $woundSize, SpecificWoundOrigin $specificWoundOrigin)
    {
        parent::__construct($health, $woundSize, $specificWoundOrigin);
    }

    /**
     * @return bool
     */
    public function isSerious()
    {
        return true;
    }

}