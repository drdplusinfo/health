<?php
namespace DrdPlus\Tests\Person\Health;

use Drd\DiceRoll\Templates\Rollers\Roller2d6DrdPlus;
use Drd\DiceRoll\Templates\Rollers\SpecificRolls\Roll2d6DrdPlus;
use DrdPlus\Person\Health\GridOfWounds;
use DrdPlus\Person\Health\Health;
use DrdPlus\Person\Health\TreatmentBoundary;
use DrdPlus\Person\Health\Wound;
use DrdPlus\Person\Health\WoundSize;
use DrdPlus\Properties\Base\Will;
use DrdPlus\Properties\Derived\WoundsLimit;
use Granam\Tests\Tools\TestWithMockery;

class HealthTest extends TestWithMockery
{

    /**
     * @test
     */
    public function I_can_use_it()
    {
        $health = new Health($this->createWoundsLimit($woundsLimitValue = 123));

        self::assertNull($health->getId());
        self::assertSame($woundsLimitValue, $health->getWoundsLimitValue());
        self::assertCount(0, $health->getUnhealedWounds());
        self::assertSame(0, $health->getUnhealedOrdinaryWoundsSum());
        self::assertSame(0, $health->getUnhealedSeriousWoundsSum());
        self::assertCount(0, $health->getAfflictions());
        self::assertSame(369, $health->getRemainingHealth());
        self::assertSame(369, $health->getHealthMaximum());
        self::assertSame(0, $health->getMalusCausedByWounds(
            $this->createWill(),
            $this->createRoll2d6Plus()
        ));
        self::assertSame(0, $health->getNumberOfSeriousInjuries());
        self::assertCount(0, $health->getPains());
        self::assertSame(0, $health->getSignificantMalus($this->createWill(), $this->createRoll2d6Plus()));
        self::assertTrue($health->isAlive());
        self::assertTrue($health->isConscious());

        self::assertInstanceOf(TreatmentBoundary::class, $health->getTreatmentBoundary());
        self::assertSame(0, $health->getTreatmentBoundary()->getValue());

        self::assertInstanceOf(GridOfWounds::class, $health->getGridOfWounds());
        self::assertSame($health->getUnhealedWoundsSum(), $health->getGridOfWounds()->getSumOfWounds());
        self::assertSame($woundsLimitValue, $health->getGridOfWounds()->getWoundsPerRowMaximum());
        self::assertSame($health->getRemainingHealth(), $health->getGridOfWounds()->getRemainingHealth());
        self::assertSame($health->getHealthMaximum(), $health->getGridOfWounds()->getHealthMaximum());
    }

    /**
     * @param $value
     * @return \Mockery\MockInterface|WoundsLimit
     */
    private function createWoundsLimit($value)
    {
        $wounds = $this->mockery(WoundsLimit::class);
        $wounds->shouldReceive('getValue')
            ->andReturn($value);

        return $wounds;
    }

    /**
     * @param int $value
     * @return \Mockery\MockInterface|Will
     */
    private function createWill($value = null)
    {
        $will = $this->mockery(Will::class);
        if ($value !== null) {
            $will->shouldReceive('getValue')
                ->andReturn($value);
        }

        return $will;
    }

    /**
     * @param $value
     * @return \Mockery\MockInterface|Roller2d6DrdPlus
     */
    private function createRoll2d6Plus($value = null)
    {
        $roller = $this->mockery(Roller2d6DrdPlus::class);
        if ($value !== null) {
            $roller->shouldReceive('roll')
                ->andReturn($roll = $this->mockery(Roll2d6DrdPlus::class));
            $roll->shouldReceive('getValue')
                ->andReturn($value);
            $roll->shouldReceive('getRolledNumbers')
                ->andReturn([$value]);
        }

        return $roller;
    }

    // TODO getUnhealedOrdinaryWoundsValue should be same value as GridOfWounds()->getSumOfWounds() - TreatmentBoundary()->getValue()

    // TODO after healSeriousAndOrdinaryWoundsUpTo the TreatmentBoundary has to be at least zero
    
    // TODO unconscious will NOT re-roll pain malus

    /**
     * @test
     */
    public function I_can_be_ordinary_wounded_and_healed()
    {
        $health = new Health($this->createWoundsLimit(3));
        self::assertSame(9, $health->getRemainingHealth());
        self::assertSame(0, $health->getMalusCausedByWounds($this->createWill(), $this->createRoll2d6Plus()));

        $wound = $health->createOrdinaryWound($this->createWoundSize(1), $this->createWill(), $this->createRoll2d6Plus());
        self::assertInstanceOf(Wound::class, $wound);
        self::assertSame(1, $wound->getValue());
        self::assertSame(8, $health->getRemainingHealth());
        self::assertSame(1, $health->getUnhealedOrdinaryWoundsSum());
        self::assertSame(0, $health->getMalusCausedByWounds($this->createWill(), $this->createRoll2d6Plus()));

        $wound = $health->createOrdinaryWound($this->createWoundSize(2), $this->createWill(), $this->createRoll2d6Plus());
        self::assertInstanceOf(Wound::class, $wound);
        self::assertSame(2, $wound->getValue());
        self::assertSame(6, $health->getRemainingHealth());
        self::assertSame(3, $health->getUnhealedOrdinaryWoundsSum());
        self::assertSame(-3, $health->getMalusCausedByWounds($this->createWill(1), $this->createRoll2d6Plus(2)));
    }

    /**
     * @param int $value
     * @return \Mockery\MockInterface|WoundSize
     */
    private function createWoundSize($value)
    {
        $woundSize = $this->mockery(WoundSize::class);
        $woundSize->shouldReceive('getValue')
            ->andReturn($value);

        return $woundSize;
    }
}
