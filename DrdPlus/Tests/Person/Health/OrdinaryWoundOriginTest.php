<?php
namespace DrdPlus\Tests\Person\Health;

use DrdPlus\Person\Health\OrdinaryWoundOrigin;
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
        self::assertTrue($ordinaryWoundOrigin->isOrdinaryWoundOrigin());
        self::assertFalse($ordinaryWoundOrigin->isSeriousWoundOrigin());
        self::assertFalse($ordinaryWoundOrigin->isMechanical()); // unknown respectively

        foreach ($this->getSeriousWoundOriginCodes() as $otherOrigin) {
            $isOtherWoundOrigin = StringTools::assembleGetterForName($otherOrigin, 'is') . 'WoundOrigin';
            self::assertFalse($ordinaryWoundOrigin->$isOtherWoundOrigin());
        }
    }

    /**
     * @test
     * @expectedException \DrdPlus\Person\Health\Exceptions\UnknownWoundOriginCode
     * @expectedExceptionMessageRegExp ~Kitchen accident~
     */
    public function I_can_not_create_custom_ordinary_origin()
    {
        OrdinaryWoundOrigin::getEnum('Kitchen accident');
    }
}
