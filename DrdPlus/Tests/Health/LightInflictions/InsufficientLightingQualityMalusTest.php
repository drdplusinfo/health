<?php
namespace DrdPlus\Health\LightInflictions;

use DrdPlus\Codes\RaceCode;
use Granam\Tests\Tools\TestWithMockery;

class InsufficientLightingQualityMalusTest extends TestWithMockery
{
    /**
     * @test
     * @dataProvider provideLightingQualityAndExpectedMalus
     * @param int $lightingQualityValue
     * @param string $raceValue
     * @param int $expectedMalus
     */
    public function I_get_malus_from_insufficient_light(
        $lightingQualityValue,
        $raceValue,
        $expectedMalus
    )
    {
        $insufficientLightingQualityMalus = new InsufficientLightingQualityMalus(
            new LightingQuality($lightingQualityValue),
            RaceCode::getIt($raceValue)
        );
        self::assertSame($expectedMalus, $insufficientLightingQualityMalus->getValue());
        self::assertSame((string)$expectedMalus, (string)$insufficientLightingQualityMalus);
    }

    public function provideLightingQualityAndExpectedMalus()
    {
        return [
            [0, RaceCode::HUMAN, 0],
            [-10, RaceCode::ELF, 0],
            [-11, RaceCode::HOBBIT, -1],
            [-19, RaceCode::HUMAN, -1],
            [-20, RaceCode::HUMAN, -2],
            [-59, RaceCode::ELF, -5],
            [-59, RaceCode::KROLL, -3],
            [-59, RaceCode::ORC, -1],
            [-59, RaceCode::DWARF, -1],
            [-100, RaceCode::HOBBIT, -10],
            [-200, RaceCode::HOBBIT, -20],
            [-999, RaceCode::DWARF, -20], // maximum is -20
            [60, RaceCode::KROLL, 0],
            [59, RaceCode::ORC, 0],
            [60, RaceCode::ORC, -2],
        ];
    }
}