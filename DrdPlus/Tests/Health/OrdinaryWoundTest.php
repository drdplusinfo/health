<?php
namespace DrdPlus\Tests\Health;

use DrdPlus\Health\Health;
use DrdPlus\Health\OrdinaryWound;
use DrdPlus\Health\SeriousWoundOrigin;
use DrdPlus\Health\Wound;
use DrdPlus\Health\WoundSize;

class OrdinaryWoundTest extends WoundTest
{
    /**
     * @param Health $health
     * @param WoundSize $woundSize
     * @param SeriousWoundOrigin $seriousWoundOrigin
     * @return OrdinaryWound
     */
    protected function createWound(Health $health, WoundSize $woundSize, SeriousWoundOrigin $seriousWoundOrigin): OrdinaryWound
    {
        return new OrdinaryWound($health, $woundSize);
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