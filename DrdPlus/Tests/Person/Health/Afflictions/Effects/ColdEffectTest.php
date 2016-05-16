<?php
namespace DrdPlus\Tests\Person\Health\Afflictions\Effects;

use DrdPlus\Person\Health\Afflictions\Effects\ColdEffect;
use DrdPlus\Person\Health\Afflictions\SpecificAfflictions\Cold;
use DrdPlus\Properties\Body\Size;

class ColdEffectTest extends AfflictionEffectTest
{

    /**
     * @test
     */
    public function I_can_get_strength_agility_and_knack_malus()
    {
        $coldEffect = ColdEffect::getIt();

        self::assertSame(0, $coldEffect->getStrengthAdjustment($this->createCold(0)));
        self::assertSame(-1, $coldEffect->getStrengthAdjustment($this->createCold(1)));
        self::assertSame(-1, $coldEffect->getStrengthAdjustment($this->createCold(4)));
        self::assertSame(-3, $coldEffect->getStrengthAdjustment($this->createCold(11)));
        self::assertSame(-4, $coldEffect->getStrengthAdjustment($this->createCold(13)));

        self::assertSame(0, $coldEffect->getAgilityAdjustment($this->createCold(0)));
        self::assertSame(-1, $coldEffect->getAgilityAdjustment($this->createCold(1)));
        self::assertSame(-1, $coldEffect->getAgilityAdjustment($this->createCold(4)));
        self::assertSame(-3, $coldEffect->getAgilityAdjustment($this->createCold(11)));
        self::assertSame(-4, $coldEffect->getAgilityAdjustment($this->createCold(13)));

        self::assertSame(0, $coldEffect->getKnackAdjustment($this->createCold(0)));
        self::assertSame(-1, $coldEffect->getKnackAdjustment($this->createCold(1)));
        self::assertSame(-1, $coldEffect->getKnackAdjustment($this->createCold(4)));
        self::assertSame(-3, $coldEffect->getKnackAdjustment($this->createCold(11)));
        self::assertSame(-4, $coldEffect->getKnackAdjustment($this->createCold(13)));
    }

    /**
     * @param int $coldSize
     * @return \Mockery\MockInterface|Cold
     */
    private function createCold($coldSize)
    {
        $cold = $this->mockery(Cold::class);
        $cold->shouldReceive('getSize')
            ->andReturn(Size::getIt($coldSize));

        return $cold;
    }

    /**
     * @test
     */
    public function I_can_find_out_if_apply_even_on_success_against_trap()
    {
        $coldEffect = ColdEffect::getIt();

        self::assertFalse($coldEffect->isEffectiveEvenOnSuccessAgainstTrap());
    }
}
