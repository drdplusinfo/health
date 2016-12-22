<?php
namespace DrdPlus\Health\LightInflictions;

use Granam\Tests\Tools\TestWithMockery;

class InsufficientLightingQualityMalusTest extends TestWithMockery
{
    /**
     * @test
     * @dataProvider provideLightingQualityAndExpectedMalus
     * @param int $lightingQualityValue
     * @param int $expectedMalus
     */
    public function I_get_malus_from_insufficient_light($lightingQualityValue, $expectedMalus)
    {
        $insufficientLightingQualityMalus = new InsufficientLightingQualityMalus(
            new LightingQuality($lightingQualityValue)
        );
        self::assertSame($expectedMalus, $insufficientLightingQualityMalus->getValue());
    }

    public function provideLightingQualityAndExpectedMalus()
    {
        return [
            [0, 0],
            [-10, 0],
            [-11, -1],
            [-19, -1],
            [-20, -2],
            [-100, -10],
            [-200, -20],
            [-999, -20], // maximum is -20
        ];
    }
}