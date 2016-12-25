<?php
namespace DrdPlus\Health\LightInflictions;

use DrdPlus\Codes\RaceCode;
use DrdPlus\Skills\Combined\DuskSight;
use Granam\Tests\Tools\TestWithMockery;

class UnsuitableLightingQualityMalusTest extends TestWithMockery
{
    /**
     * @test
     * @dataProvider provideLightingQualityAndExpectedMalus
     * @param int $lightingQualityValue
     * @param int $barrierOpacityValue
     * @param string $raceValue
     * @param int $fromDuskSightBonus
     * @param int $expectedMalus
     */
    public function I_get_malus_from_insufficient_light(
        $lightingQualityValue,
        $barrierOpacityValue,
        $raceValue,
        $fromDuskSightBonus,
        $expectedMalus
    )
    {
        $insufficientLightingQualityMalus = new UnsuitableLightingQualityMalus(
            new LightingQuality($lightingQualityValue),
            $this->createOpacity($barrierOpacityValue),
            RaceCode::getIt($raceValue),
            $this->createDuskSight($fromDuskSightBonus)
        );
        self::assertSame($expectedMalus, $insufficientLightingQualityMalus->getValue());
        self::assertSame((string)$expectedMalus, (string)$insufficientLightingQualityMalus);
    }

    public function provideLightingQualityAndExpectedMalus()
    {
        // note: orcs and dwarfs have +4 bonus in darkness, krolls +2 but orcs have -2 malus on bright light
        return [
            [0, -200, RaceCode::HUMAN, 0, 0],
            [0, 0, RaceCode::HUMAN, 0, 0],
            [-10, 0, RaceCode::ELF, 0, 0],
            [-11, 0, RaceCode::HOBBIT, 0, -1],
            [-11, 0, RaceCode::HOBBIT, 1, 0],
            [-19, 0, RaceCode::HUMAN, 0, -1],
            [-20, 0, RaceCode::HUMAN, 0, -2],
            [-59, 20, RaceCode::ELF, 0, -7],
            [-59, 0, RaceCode::KROLL, 0, -3],
            [-59, 0, RaceCode::ORC, 0, -1],
            [-59, 0, RaceCode::DWARF, 0, -1],
            [-100, 0, RaceCode::HOBBIT, 0, -10],
            [-200, 0, RaceCode::HOBBIT, 0, -20],
            [-200, 0, RaceCode::ORC, 3, -13],
            [-999, 0, RaceCode::DWARF, 90, -5],
            [-999, 0, RaceCode::DWARF, 0, -20], // maximum is -20
            [60, 0, RaceCode::KROLL, 0, 0],
            [59, 0, RaceCode::ORC, 0, 0],
            [60, 0, RaceCode::ORC, 0, -2],
            [61, 1, RaceCode::ORC, 0, -2],
            [61, 2, RaceCode::ORC, 0, 0],
        ];
    }

    /**
     * @param int $value
     * @return \Mockery\MockInterface|Opacity
     */
    private function createOpacity($value)
    {
        $opacity = $this->mockery(Opacity::class);
        $opacity->shouldReceive('getValue')
            ->andReturn($value);

        return $opacity;
    }

    /**
     * @param int $bonus
     * @return \Mockery\MockInterface|DuskSight
     */
    private function createDuskSight($bonus)
    {
        $duskSight = $this->mockery(DuskSight::class);
        $duskSight->shouldReceive('getInsufficientLightingBonus')
            ->andReturn($bonus);

        return $duskSight;
    }
}