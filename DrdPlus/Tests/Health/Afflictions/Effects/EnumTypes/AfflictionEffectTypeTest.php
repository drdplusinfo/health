<?php
namespace DrdPlus\Tests\Health\Afflictions\Effects\EnumTypes;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrineum\Tests\SelfRegisteringType\AbstractSelfRegisteringTypeTest;
use DrdPlus\Health\Afflictions\Effects\ColdEffect;
use DrdPlus\Health\Afflictions\Effects\EnumTypes\AfflictionEffectType;
use DrdPlus\Health\Afflictions\Effects\SeveredArmEffect;

class AfflictionEffectTypeTest extends AbstractSelfRegisteringTypeTest
{
    /**
     * @test
     * @dataProvider provideEffectCodeAndClass
     * @param $effectCode
     * @param $expectedEffectClass
     */
    public function I_can_use_safely_all_effects($effectCode, $expectedEffectClass)
    {
        AfflictionEffectType::registerSelf();
        $afflictionEffect = Type::getType(AfflictionEffectType::AFFLICTION_EFFECT);
        self::assertInstanceOf($expectedEffectClass, $afflictionEffect->convertToPHPValue($effectCode, $this->createPlatform()));
    }

    public function provideEffectCodeAndClass()
    {
        return [
            [ColdEffect::COLD_EFFECT, ColdEffect::class],
            [SeveredArmEffect::SEVERED_ARM_EFFECT, SeveredArmEffect::class],
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
