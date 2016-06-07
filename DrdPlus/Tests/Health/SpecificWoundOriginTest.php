<?php
namespace DrdPlus\Tests\Health;

use DrdPlus\Health\SpecificWoundOrigin;
use DrdPlus\Health\WoundOrigin;
use Granam\String\StringTools;

class SpecificWoundOriginTest extends WoundOriginTest
{
    /**
     * @test
     * @dataProvider provideOriginCode
     * @param string $originName
     */
    public function I_can_get_every_type_of_serious_wound($originName)
    {
        $getWoundOrigin = StringTools::assembleGetterForName($originName) . 'WoundOrigin';
        /** @var WoundOrigin $woundOrigin */
        $woundOrigin = SpecificWoundOrigin::$getWoundOrigin();

        $isWoundOrigin = StringTools::assembleGetterForName($originName, 'is') . 'WoundOrigin';
        self::assertTrue($woundOrigin->$isWoundOrigin());
        self::assertFalse($woundOrigin->isOrdinaryWoundOrigin());
        self::assertTrue($woundOrigin->isSeriousWoundOrigin());
        self::assertSame(strpos($originName, 'mechanical') !== false, $woundOrigin->isMechanical());

        $otherOrigins = array_diff($this->getSeriousWoundOriginCodes(), [$originName]);
        foreach ($otherOrigins as $otherOrigin) {
            $isOtherWoundOrigin = StringTools::assembleGetterForName($otherOrigin, 'is') . 'WoundOrigin';
            self::assertFalse($woundOrigin->$isOtherWoundOrigin());
        }
    }
    public function provideOriginCode()
    {
        return array_map(
            function ($code) {
                return [$code];
            },
            $this->getSeriousWoundOriginCodes()
        );
    }

    /**
     * @test
     * @expectedException \DrdPlus\Health\Exceptions\UnknownWoundOriginCode
     * @expectedExceptionMessageRegExp ~Kitchen accident~
     */
    public function I_can_not_create_custom_origin()
    {
        SpecificWoundOrigin::getEnum('Kitchen accident');
    }
}
