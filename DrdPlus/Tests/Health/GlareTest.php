<?php
namespace DrdPlus\Tests\Health;

use DrdPlus\Health\Contrast;
use DrdPlus\Health\Glare;
use DrdPlus\RollsOn\Traps\RollOnSenses;
use Granam\Tests\Tools\TestWithMockery;

class GlareTest extends TestWithMockery
{
    /**
     * @test
     * @dataProvider provideContrastRollOnSensesAndMalus
     * @param int $contrastValue
     * @param bool $fromDarkToLight
     * @param int $rollOnSensesValue
     * @param int $expectedMalus
     */
    public function I_can_get_malus_from_glare($contrastValue, $fromDarkToLight ,$rollOnSensesValue, $expectedMalus)
    {
        $glare = new Glare($this->createContrast($contrastValue, $fromDarkToLight), $this->createRollOnSenses($rollOnSensesValue));
        self::assertSame($expectedMalus, $glare->getMalus());
        self::assertSame($fromDarkToLight, $glare->isShined());
        self::assertSame(!$fromDarkToLight, $glare->isBlinded());
    }

    public function provideContrastRollOnSensesAndMalus()
    {
        return [
            [123, true, 21, -116], // - (123 - 7)
            [123, false, 985, -122], // - (123 - 1)
            [-456, true, 654, 0],
            [0, true, 1, 0],
            [1, false, 35, 0],
            [2, false, 35, -1], // - (2 - 1)
        ];
    }

    /**
     * @param int $value
     * @param bool $fromDarkToLight
     * @return \Mockery\MockInterface|Contrast
     */
    private function createContrast($value, $fromDarkToLight)
    {
        $contrast = $this->mockery(Contrast::class);
        $contrast->shouldReceive('getValue')
            ->andReturn($value);
        $contrast->shouldReceive('isFromDarkToLight')
            ->andReturn($fromDarkToLight);
        $contrast->shouldReceive('isFromLightToDark')
            ->andReturn(!$fromDarkToLight);

        return $contrast;
    }

    /**
     * @param int $value
     * @return \Mockery\MockInterface|RollOnSenses
     */
    private function createRollOnSenses($value)
    {
        $contrast = $this->mockery(RollOnSenses::class);
        $contrast->shouldReceive('getValue')
            ->andReturn($value);

        return $contrast;
    }
}