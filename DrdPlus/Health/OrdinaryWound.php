<?php
namespace DrdPlus\Health;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 */
class OrdinaryWound extends Wound
{
    /**
     * @param Health $health
     * @param WoundSize $woundSize
     * @throws \DrdPlus\Health\Exceptions\WoundHasToBeCreatedByHealthItself
     */
    public function __construct(Health $health, WoundSize $woundSize)
    {
        parent::__construct($health, $woundSize, OrdinaryWoundOrigin::getIt());
    }

    /**
     * @return bool
     */
    public function isSerious(): bool
    {
        return false;
    }

}