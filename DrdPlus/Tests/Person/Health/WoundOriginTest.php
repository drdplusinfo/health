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
     * @param bool $isOrdinary
     */
    public function I_can_get_every_type_of_wound($originName, $isOrdinary)
    {
        $getWoundOrigin = StringTools::assembleGetterForName($originName) . 'WoundOrigin';
        /** @var WoundOrigin $woundOrigin */
        $woundOrigin = WoundOrigin::$getWoundOrigin();

        $isWoundOrigin = StringTools::assembleGetterForName($originName, 'is') . 'WoundOrigin';
        self::assertTrue($woundOrigin->$isWoundOrigin());
        self::assertSame($isOrdinary, $woundOrigin->isOrdinaryWoundOrigin());
        self::assertSame(!$isOrdinary, $woundOrigin->isExtraOrdinaryWoundOrigin());

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
                return [$code, strpos($code, 'ordinary') !== false];
            },
            $this->getWoundOriginCodes()
        );
    }

    /**
     * @test
     * @expectedException \DrdPlus\Person\Health\Exceptions\UnknownWoundOriginCode
     * @expectedExceptionMessageRegExp ~Kitchen accident~
     */
    public function I_can_not_create_custom_origin()
    {
        WoundOrigin::getEnum('Kitchen accident');
    }
}
