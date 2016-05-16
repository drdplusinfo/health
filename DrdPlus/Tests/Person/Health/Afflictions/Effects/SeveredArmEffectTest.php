<?php
namespace DrdPlus\Tests\Person\Health\Afflictions\Effects;

use DrdPlus\Person\Health\Afflictions\AfflictionSize;
use DrdPlus\Person\Health\Afflictions\Effects\SeveredArmEffect;
use DrdPlus\Person\Health\Afflictions\SpecificAfflictions\SeveredArm;

class SeveredArmEffectTest extends AfflictionEffectTest
{
    /**
     * @test
     */
    public function I_can_find_out_if_apply_even_on_success_against_trap()
    {
        $severedArmEffect = SeveredArmEffect::getIt();
        self::assertTrue($severedArmEffect->isEffectiveEvenOnSuccessAgainstTrap());
    }

    /**
     * @test
     */
    public function I_can_get_strength_and_knack_malus()
    {
        $severedArmEffect = SeveredArmEffect::getIt();
        self::assertSame(-123, $severedArmEffect->getStrengthAdjustment($this->createSeveredArm(123)));
        self::assertSame(-246, $severedArmEffect->getKnackAdjustment($this->createSeveredArm(123)));
    }

    /**
     * @param $serverArmSize
     * @return \Mockery\MockInterface|SeveredArm
     */
    private function createSeveredArm($serverArmSize)
    {
        $severedArm = $this->mockery(SeveredArm::class);
        $severedArm->shouldReceive('getSize')
            ->andReturn(AfflictionSize::getIt($serverArmSize));

        return $severedArm;
    }

}
