<?php
namespace DrdPlus\Tests\Person\Health;

use DrdPlus\Person\Health\Health;
use DrdPlus\Person\Health\OrdinaryWound;
use DrdPlus\Person\Health\OrdinaryWoundOrigin;
use DrdPlus\Person\Health\SpecificWoundOrigin;
use DrdPlus\Person\Health\Wound;
use DrdPlus\Person\Health\WoundSize;

class OrdinaryWoundTest extends WoundTest
{
    /**
     * @param Health $health
     * @param WoundSize $woundSize
     * @param SpecificWoundOrigin $specificWoundOrigin
     * @return OrdinaryWound
     */
    protected function createWound(Health $health, WoundSize $woundSize, SpecificWoundOrigin $specificWoundOrigin)
    {
        return new OrdinaryWound($health, $woundSize, OrdinaryWoundOrigin::getIt());
    }

    /**
     * @param Wound $wound
     */
    protected function assertIsSeriousAsExpected(Wound $wound)
    {
        self::assertInstanceOf(OrdinaryWound::class, $wound);
        self::assertFalse($wound->isSerious(), 'Ordinary wound should not be serious wound');
    }
}