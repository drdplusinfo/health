<?php
namespace DrdPlus\Tests\Health\EnumTypes;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrineum\Tests\SelfRegisteringType\AbstractSelfRegisteringTypeTest;
use DrdPlus\Health\EnumTypes\WoundOriginType;
use DrdPlus\Health\OrdinaryWoundOrigin;
use DrdPlus\Health\SeriousWoundOrigin;

class WoundOriginTypeTest extends AbstractSelfRegisteringTypeTest
{
    /**
     * @test
     * @dataProvider provideCodeAndClass
     * @param $originCode
     * @param $expectedOriginClass
     */
    public function I_can_use_safely_all_origins($originCode, $expectedOriginClass)
    {
        WoundOriginType::registerSelf();
        $woundOrigin = WoundOriginType::getType(WoundOriginType::WOUND_ORIGIN);
        self::assertInstanceOf($expectedOriginClass, $woundOrigin->convertToPHPValue($originCode, $this->createPlatform()));
    }

    public function provideCodeAndClass()
    {
        return [
            [OrdinaryWoundOrigin::ORDINARY, OrdinaryWoundOrigin::class],
            [SeriousWoundOrigin::ELEMENTAL, SeriousWoundOrigin::class],
            [SeriousWoundOrigin::MECHANICAL_CRUSH, SeriousWoundOrigin::class],
            [SeriousWoundOrigin::MECHANICAL_CUT, SeriousWoundOrigin::class],
            [SeriousWoundOrigin::MECHANICAL_STAB, SeriousWoundOrigin::class],
            [SeriousWoundOrigin::PSYCHICAL, SeriousWoundOrigin::class],
        ];
    }

    /**
     * @return \Mockery\MockInterface|AbstractPlatform
     */
    private function createPlatform()
    {
        return $this->mockery(AbstractPlatform::class);
    }
}
