<?php
namespace DrdPlus\Tests\Person\Health;

use DrdPlus\Person\Health\Health;
use DrdPlus\Person\Health\SeriousWound;
use DrdPlus\Person\Health\SpecificWoundOrigin;
use DrdPlus\Person\Health\Wound;
use DrdPlus\Person\Health\WoundSize;

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
