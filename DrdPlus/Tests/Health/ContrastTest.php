<?php
namespace DrdPlus\Tests\Health;

use DrdPlus\Health\Contrast;
use Granam\Integer\IntegerObject;
use Granam\Tests\Tools\TestWithMockery;

class ContrastTest extends TestWithMockery
{
    /**
     * @test
     * @dataProvider providePreviousAndCurrentLightIntensity
     * @param int $previousLightIntensityValue
     * @param int $currentLightIntensityValue
     * @param int $expectedContrast
     */
    public function I_can_use_it($previousLightIntensityValue, $currentLightIntensityValue, $expectedContrast)
    {
        $contrast = new Contrast(
            new IntegerObject($previousLightIntensityValue),
            new IntegerObject($currentLightIntensityValue)
        );
        self::assertSame($expectedContrast, $contrast->getValue());
        self::assertSame(
            (string)$expectedContrast . ' (' . ($contrast->isFromLightToDark() ? 'to dark' : 'to light') . ')',
            (string)$contrast
        );
        self::assertSame($previousLightIntensityValue > $currentLightIntensityValue, $contrast->isFromLightToDark());
        self::assertSame($previousLightIntensityValue < $currentLightIntensityValue, $contrast->isFromDarkToLight());
        self::assertSame(!$contrast->isFromDarkToLight(), $contrast->isFromLightToDark());
    }

    public function providePreviousAndCurrentLightIntensity()
    {
        return [
            [-123, 345, 46], // negative, rounded up (removed decimals)
            [678, 1, 67] // positive, rounded down (removed decimals)
        ];
    }
}