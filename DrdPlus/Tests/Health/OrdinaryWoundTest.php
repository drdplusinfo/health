<?php
namespace DrdPlus\Tests\Health;

use DrdPlus\Health\Health;
use DrdPlus\Health\OrdinaryWound;
use DrdPlus\Health\OrdinaryWoundOrigin;
use DrdPlus\Health\SpecificWoundOrigin;
use DrdPlus\Health\Wound;
use DrdPlus\Health\WoundSize;

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