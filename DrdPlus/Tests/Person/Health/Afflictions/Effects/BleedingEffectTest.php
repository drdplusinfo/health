<?php
namespace DrdPlus\Tests\Person\Health\Afflictions\Effects;

use Drd\DiceRoll\Templates\Rollers\Roller2d6DrdPlus;
use DrdPlus\Person\Health\Afflictions\Effects\BleedingEffect;
use DrdPlus\Person\Health\Afflictions\SpecificAfflictions\Bleeding;
use DrdPlus\Person\Health\Health;
use DrdPlus\Person\Health\SeriousWound;
use DrdPlus\Person\Health\SpecificWoundOrigin;
use DrdPlus\Person\Health\Wound;
use DrdPlus\Person\Health\WoundSize;
use DrdPlus\Properties\Base\Will;
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
        $health = new Health($this->createWoundsLimit(10));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $woundCausedBleeding = $health->createWound(
            new WoundSize(25),
            $specificWoundOrigin = $this->createSpecificWoundOrigin()
        );
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $wound = $bleedingEffect->bleed(
            Bleeding::createIt($woundCausedBleeding),
            new WoundsTable(),
            $this->createWill(),
            $this->createRoller2d6DrdPlus()
        );
        self::assertInstanceOf(Wound::class, $wound);
        self::assertSame(3, $wound->getValue()); // 4 bleeding size ... some calculation ... see wounds table for details
        self::assertTrue($wound->getWoundOrigin()->isOrdinaryWoundOrigin()); // because not a serious injury
        self::assertNotEquals($specificWoundOrigin, $wound->getWoundOrigin());
    }

    /**
     * @param $value
     * @return \Mockery\MockInterface|WoundBoundary
     */
    private function createWoundsLimit($value)
    {
        $woundsLimit = $this->mockery(WoundBoundary::class);
        $woundsLimit->shouldReceive('getValue')
            ->andReturn($value);

        return $woundsLimit;
    }

    /**
     * @return \Mockery\MockInterface|SpecificWoundOrigin
     */
    private function createSpecificWoundOrigin()
    {
        $specificWoundOrigin = $this->mockery(SpecificWoundOrigin::class);
        $specificWoundOrigin->shouldReceive('isOrdinaryWoundOrigin')
            ->andReturn(false);

        return $specificWoundOrigin;
    }

    /**
     * @return \Mockery\MockInterface|Will
     */
    private function createWill()
    {
        return $this->mockery(Will::class);
    }

    /**
     * @return \Mockery\MockInterface|Roller2d6DrdPlus
     */
    private function createRoller2d6DrdPlus()
    {
        return $this->mockery(Roller2d6DrdPlus::class);
    }
}
