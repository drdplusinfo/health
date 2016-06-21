<?php
namespace DrdPlus\Tests\Health;

use DrdPlus\Health\OrdinaryWoundOrigin;
use Granam\String\StringTools;

class OrdinaryWoundOriginTest extends WoundOriginTest
{
    /**
     * @test
     */
    public function I_can_use_it()
    {
        $ordinaryWoundOrigin = OrdinaryWoundOrigin::getIt();
        self::assertSame($ordinaryWoundOrigin, OrdinaryWoundOrigin::getEnum('ordinary'));
        self::assertTrue($ordinaryWoundOrigin->isOrdinaryWoundOrigin());
        self::assertFalse($ordinaryWoundOrigin->isSeriousWoundOrigin());

        foreach ($this->getSeriousWoundOriginCodes() as $otherOrigin) {
            $isOtherWoundOrigin = StringTools::assembleGetterForName($otherOrigin, 'is') . 'WoundOrigin';
            self::assertFalse($ordinaryWoundOrigin->$isOtherWoundOrigin());
        }
    }

    /**
     * @test
     * @expectedException \DrdPlus\Health\Exceptions\UnknownWoundOriginCode
     * @expectedExceptionMessageRegExp ~Kitchen accident~
     */
    public function I_can_not_create_custom_ordinary_origin()
    {
        OrdinaryWoundOrigin::getEnum('Kitchen accident');
    }
}
