<?php
namespace DrdPlus\Health;

use Doctrine\ORM\Mapping AS ORM;
use DrdPlus\Codes\Body\SeriousWoundOriginCode;

/**
 * @ORM\Entity
 */
class SeriousWound extends Wound
{
    /**
     * @param Health $health
     * @param WoundSize $woundSize
     * @param SeriousWoundOriginCode $seriousWoundOriginCode
     * @throws \DrdPlus\Health\Exceptions\WoundHasToBeCreatedByHealthItself
     */
    public function __construct(Health $health, WoundSize $woundSize, SeriousWoundOriginCode $seriousWoundOriginCode)
    {
        parent::__construct($health, $woundSize, $seriousWoundOriginCode);
    }

    /**
     * @return bool
     */
    public function isSerious(): bool
    {
        return true;
    }

    public function isOrdinary(): bool
    {
        return false;
    }

}