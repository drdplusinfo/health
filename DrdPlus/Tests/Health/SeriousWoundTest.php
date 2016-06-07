<?php
namespace DrdPlus\Tests\Health;

use DrdPlus\Health\Health;
use DrdPlus\Health\SeriousWound;
use DrdPlus\Health\SpecificWoundOrigin;
use DrdPlus\Health\Wound;
use DrdPlus\Health\WoundSize;

class SeriousWoundTest extends WoundTest
{
    /**
     * @param Health $health
     * @param WoundSize $woundSize
     * @param SpecificWoundOrigin $specificWoundOrigin
     * @return SeriousWound
     */
    protected function createWound(Health $health, WoundSize $woundSize, SpecificWoundOrigin $specificWoundOrigin)
    {
        return new SeriousWound($health, $woundSize, $specificWoundOrigin);
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
