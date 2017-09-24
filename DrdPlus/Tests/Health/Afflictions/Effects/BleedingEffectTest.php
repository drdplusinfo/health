<?php
namespace DrdPlus\Tests\Health\Afflictions\Effects;

use DrdPlus\Health\Afflictions\Effects\BleedingEffect;
use DrdPlus\Health\Afflictions\SpecificAfflictions\Bleeding;
use DrdPlus\Health\Health;
use DrdPlus\Health\SeriousWoundOrigin;
use DrdPlus\Health\Wound;
use DrdPlus\Health\WoundSize;
use DrdPlus\Properties\Derived\WoundBoundary;
use DrdPlus\Tables\Measurements\Wounds\WoundsTable;

/** @noinspection LongInheritanceChainInspection */
class BleedingEffectTest extends AfflictionEffectTest
{
    /**
     * @test
     */
    public function I_can_find_out_if_apply_even_on_success_against_trap()
    {
        $bleedingEffect = BleedingEffect::getIt();
        self::assertTrue($bleedingEffect->isEffectiveEvenOnSuccessAgainstTrap());
    }

    /**
     * @test
     */
    public function I_can_get_wound_caused_by_bleeding()
    {
        $bleedingEffect = BleedingEffect::getIt();
        $health = new Health();
        $woundBoundary = $this->createWoundLimitBoundary(10);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $woundCausedBleeding = $health->createWound(
            new WoundSize(25),
            $seriousWoundOrigin = $this->createSpecificWoundOrigin(),
            $woundBoundary
        );
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $wound = $bleedingEffect->bleed(
            Bleeding::createIt($woundCausedBleeding, $woundBoundary),
            new WoundsTable(),
            $woundBoundary
        );
        self::assertInstanceOf(Wound::class, $wound);
        self::assertSame(3, $wound->getValue()); // 4 bleeding size ... some calculation ... see wounds table for details
        self::assertTrue($wound->getWoundOrigin()->isOrdinaryWoundOrigin()); // because not a serious injury
        self::assertNotEquals($seriousWoundOrigin, $wound->getWoundOrigin());
    }

    /**
     * @param $value
     * @return \Mockery\MockInterface|WoundBoundary
     */
    private function createWoundLimitBoundary($value)
    {
        $woundsLimit = $this->mockery(WoundBoundary::class);
        $woundsLimit->shouldReceive('getValue')
            ->andReturn($value);

        return $woundsLimit;
    }

    /**
     * @return \Mockery\MockInterface|SeriousWoundOrigin
     */
    private function createSpecificWoundOrigin()
    {
        $seriousWoundOrigin = $this->mockery(SeriousWoundOrigin::class);
        $seriousWoundOrigin->shouldReceive('isOrdinaryWoundOrigin')
            ->andReturn(false);

        return $seriousWoundOrigin;
    }
}
