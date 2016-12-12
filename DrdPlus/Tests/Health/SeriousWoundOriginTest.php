<?php
namespace DrdPlus\Tests\Health;

use DrdPlus\Health\SeriousWoundOrigin;
use Granam\String\StringTools;

class SeriousWoundOriginTest extends WoundOriginTest
{
    /**
     * @test
     * @dataProvider provideSeriousWoundOriginCode
     * @param string $seriousWoundOriginName
     */
    public function I_can_get_every_type_of_serious_wound_origin($seriousWoundOriginName)
    {
        $getWoundOrigin = StringTools::assembleGetterForName($seriousWoundOriginName) . 'WoundOrigin';
        /** @var SeriousWoundOrigin $seriousWoundOrigin */
        $seriousWoundOrigin = SeriousWoundOrigin::$getWoundOrigin();

        $isWoundOrigin = StringTools::assembleGetterForName($seriousWoundOriginName, 'is') . 'WoundOrigin';
        self::assertTrue($seriousWoundOrigin->$isWoundOrigin());
        self::assertFalse($seriousWoundOrigin->isOrdinaryWoundOrigin());
        self::assertTrue($seriousWoundOrigin->isSeriousWoundOrigin());
        self::assertSame(strpos($seriousWoundOriginName, 'mechanical') !== false, $seriousWoundOrigin->isMechanical());

        $otherOrigins = array_diff($this->getSeriousWoundOriginCodes(), [$seriousWoundOriginName]);
        foreach ($otherOrigins as $otherOrigin) {
            $isOtherWoundOrigin = StringTools::assembleGetterForName($otherOrigin, 'is') . 'WoundOrigin';
            self::assertFalse($seriousWoundOrigin->$isOtherWoundOrigin());
        }
    }

    public function provideSeriousWoundOriginCode()
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
     * @expectedExceptionMessageRegExp ~Bathroom slipping~
     */
    public function I_can_not_create_custom_origin()
    {
        SeriousWoundOrigin::getEnum('Bathroom slipping');
    }
}