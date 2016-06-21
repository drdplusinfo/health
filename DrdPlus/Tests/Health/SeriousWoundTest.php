<?php
namespace DrdPlus\Tests\Health;

use DrdPlus\Health\Health;
use DrdPlus\Health\SeriousWound;
use DrdPlus\Health\SeriousWoundOrigin;
use DrdPlus\Health\Wound;
use DrdPlus\Health\WoundSize;

class SeriousWoundTest extends WoundTest
{
    /**
     * @param Health $health
     * @param WoundSize $woundSize
     * @param SeriousWoundOrigin $seriousWoundOrigin
     * @return SeriousWound
     */
    protected function createWound(Health $health, WoundSize $woundSize, SeriousWoundOrigin $seriousWoundOrigin)
    {
        return new SeriousWound($health, $woundSize, $seriousWoundOrigin);
    }

    /**
     * @param Wound $wound
     */
    protected function assertIsSeriousAsExpected(Wound $wound)
    {
        self::assertInstanceOf(SeriousWound::class, $wound);
        self::assertTrue($wound->isSerious());
    }

}
