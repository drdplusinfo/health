<?php
namespace DrdPlus\Tests\Health\LightInflictions;

use DrdPlus\Health\LightInflictions\Contrast;
use DrdPlus\Health\LightInflictions\LightingQuality;
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
            new LightingQuality($previousLightIntensityValue),
            new LightingQuality($currentLightIntensityValue)
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