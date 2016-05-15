<?php
namespace DrdPlus\Tests\Person\Health;

use DrdPlus\Codes\WoundsOriginCodes;
use DrdPlus\Person\Health\WoundOrigin;
use Granam\String\StringTools;
use Granam\Tests\Tools\TestWithMockery;

class WoundOriginTest extends TestWithMockery
{
    /**
     * @test
     * @dataProvider provideOriginCode
     * @param string $originName
     */
    public function I_can_get_every_type_of_wound($originName)
    {
        $getWoundOrigin = StringTools::assembleGetterForName($originName) . 'WoundOrigin';
        $woundOrigin = WoundOrigin::$getWoundOrigin();

        $isWoundOrigin = StringTools::assembleGetterForName($originName, 'is') . 'WoundOrigin';
        self::assertTrue($woundOrigin->$isWoundOrigin());

        $otherOrigins = array_diff($this->getWoundOriginCodes(), [$originName]);
        foreach ($otherOrigins as $otherOrigin) {
            $isOtherWoundOrigin = StringTools::assembleGetterForName($otherOrigin, 'is') . 'WoundOrigin';
            self::assertFalse($woundOrigin->$isOtherWoundOrigin());
        }
    }

    /**
     * @return array|string[]
     */
    private function getWoundOriginCodes()
    {
        return array_merge(WoundsOriginCodes::getOriginWithTypeCodes(), [WoundOrigin::ORDINARY]);
    }

    public function provideOriginCode()
    {
        return array_map(
            function ($code) {
                return [$code]; // just wrapping into array
            },
            $this->getWoundOriginCodes()
        );
    }
}
