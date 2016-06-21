<?php
namespace DrdPlus\Tests\Health;

use DrdPlus\Health\OrdinaryWoundOrigin;

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
