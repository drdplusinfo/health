<?php
namespace DrdPlus\Tests\Person\Health;

use Drd\DiceRoll\Templates\Rollers\Roller2d6DrdPlus;
use Drd\DiceRoll\Templates\Rollers\SpecificRolls\Roll2d6DrdPlus;
use DrdPlus\Person\Health\Afflictions\AfflictionByWound;
use DrdPlus\Person\Health\GridOfWounds;
use DrdPlus\Person\Health\Health;
use DrdPlus\Person\Health\TreatmentBoundary;
use DrdPlus\Person\Health\Wound;
use DrdPlus\Person\Health\WoundOrigin;
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
        self::assertSame(369, $health->getRemainingHealthAmount());
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
        self::assertSame($health->getRemainingHealthAmount(), $health->getGridOfWounds()->getRemainingHealth());
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
        $health = new Health($this->createWoundsLimit(5));
        self::assertSame(15, $health->getRemainingHealthAmount());
        self::assertSame(0, $health->getMalusCausedByWounds($this->createWill(), $this->createRoll2d6Plus()));
        self::assertTrue($health->isAlive());
        self::assertTrue($health->isConscious());

        $wound = $health->createWound($this->createWoundSize(1), $this->createWill(), $this->createRoll2d6Plus());
        self::assertInstanceOf(Wound::class, $wound);
        self::assertSame(1, $wound->getValue());
        self::assertCount(1, $health->getUnhealedWounds());
        self::assertSame($wound, $health->getUnhealedWounds()->current());
        self::assertSame(14, $health->getRemainingHealthAmount());
        self::assertSame(1, $health->getUnhealedOrdinaryWoundsSum());
        self::assertSame(0, $health->getMalusCausedByWounds($this->createWill(), $this->createRoll2d6Plus()));
        self::assertTrue($health->isAlive());
        self::assertTrue($health->isConscious());

        $wound = $health->createWound($this->createWoundSize(2), $this->createWill(), $this->createRoll2d6Plus());
        self::assertInstanceOf(Wound::class, $wound);
        self::assertSame(2, $wound->getValue());
        self::assertCount(2, $health->getUnhealedWounds());
        $woundSum = 0;
        $collectedWounds = [];
        foreach ($health->getUnhealedWounds() as $unhealedWound) {
            self::assertInstanceOf(Wound::class, $unhealedWound);
            self::assertLessThanOrEqual(2, $unhealedWound->getValue());
            $woundSum += $unhealedWound->getValue();
            $collectedWounds[] = $unhealedWound;
        }
        $collectedWounds = $this->sortWoundsByValue($collectedWounds);
        $wounds = $this->sortWoundsByValue($health->getUnhealedWounds()->toArray());
        self::assertSame($wounds, $collectedWounds);
        self::assertSame(3, $woundSum);
        self::assertSame(12, $health->getRemainingHealthAmount());
        self::assertSame(3, $health->getUnhealedOrdinaryWoundsSum());
        self::assertSame(0, $health->getMalusCausedByWounds($this->createWill(1), $this->createRoll2d6Plus(2)));
        self::assertTrue($health->isAlive());
        self::assertTrue($health->isConscious());

        $health->createWound($this->createWoundSize(2), $this->createWill(), $this->createRoll2d6Plus());
        self::assertCount(3, $health->getUnhealedWounds());
        $woundSum = 0;
        $collectedWounds = [];
        foreach ($health->getUnhealedWounds() as $unhealedWound) {
            self::assertInstanceOf(Wound::class, $unhealedWound);
            self::assertLessThanOrEqual(2, $unhealedWound->getValue());
            $woundSum += $unhealedWound->getValue();
            $collectedWounds[] = $unhealedWound;
        }
        $collectedWounds = $this->sortWoundsByValue($collectedWounds);
        $wounds = $this->sortWoundsByValue($health->getUnhealedWounds()->toArray());
        self::assertSame($wounds, $collectedWounds);
        self::assertSame(5, $woundSum);
        self::assertSame(10, $health->getRemainingHealthAmount());
        self::assertSame(5, $health->getUnhealedOrdinaryWoundsSum());
        self::assertSame(-3, $health->getMalusCausedByWounds($this->createWill(1), $this->createRoll2d6Plus(2)));
        self::assertTrue($health->isAlive());
        self::assertTrue($health->isConscious());

        self::assertSame(1, $health->healOrdinaryWoundsUpTo(1));
        self::assertSame(11, $health->getRemainingHealthAmount());
        self::assertSame(4, $health->getUnhealedOrdinaryWoundsSum());
        self::assertSame(0, $health->getUnhealedSeriousWoundsSum());
        self::assertSame(0, $health->getNumberOfSeriousInjuries());
        self::assertSame(0, $health->getMalusCausedByWounds($this->createWill(), $this->createRoll2d6Plus()));
        self::assertTrue($health->isAlive());
        self::assertTrue($health->isConscious());

        self::assertSame(4, $health->healSeriousAndOrdinaryWoundsUpTo(999));
        self::assertSame(15, $health->getRemainingHealthAmount());
        self::assertCount(0, $health->getUnhealedWounds());
        self::assertSame(0, $health->getUnhealedOrdinaryWoundsSum());
        self::assertSame(0, $health->getUnhealedSeriousWoundsSum());
        self::assertSame(0, $health->getNumberOfSeriousInjuries());
        self::assertSame(0, $health->getMalusCausedByWounds($this->createWill(), $this->createRoll2d6Plus()));
        self::assertTrue($health->isAlive());
        self::assertTrue($health->isConscious());
    }

    /**
     * @test
     */
    public function I_can_be_seriously_wounded_and_healed()
    {
        $health = new Health($this->createWoundsLimit(6));
        self::assertSame(18, $health->getRemainingHealthAmount());
        self::assertSame(0, $health->getMalusCausedByWounds($this->createWill(), $this->createRoll2d6Plus()));
        self::assertTrue($health->isAlive());
        self::assertTrue($health->isConscious());

        // TODO to serious wounds
        $wound = new Wound($health, $this->createWoundSize(0), $this->createWoundOrigin());
        $health->addAffliction(
            $this->createWoundSize(0),
            $woundOrigin = '',
            $affliction = $this->createAffliction(),
            $this->createWill(),
            $this->createRoll2d6Plus()
        );
        self::assertInstanceOf(Wound::class, $wound);
        self::assertSame(0, $wound->getValue());
        self::assertSame($woundOrigin, $wound->getWoundOrigin());
        self::assertCount(1, $health->getAfflictions());
        self::assertSame($affliction, $health->getAfflictions()->current());

        self::assertCount(1, $health->getUnhealedWounds());
        self::assertSame($wound, $health->getUnhealedWounds()->current());
        self::assertSame(17, $health->getRemainingHealthAmount());
        self::assertSame(1, $health->getUnhealedOrdinaryWoundsSum());
        self::assertSame(0, $health->getMalusCausedByWounds($this->createWill(), $this->createRoll2d6Plus()));
        self::assertTrue($health->isAlive());
        self::assertTrue($health->isConscious());

        $wound = $health->createWound($this->createWoundSize(2), $this->createWill(), $this->createRoll2d6Plus());
        self::assertInstanceOf(Wound::class, $wound);
        self::assertSame(2, $wound->getValue());
        self::assertCount(2, $health->getUnhealedWounds());
        $woundSum = 0;
        $collectedWounds = [];
        foreach ($health->getUnhealedWounds() as $unhealedWound) {
            self::assertInstanceOf(Wound::class, $unhealedWound);
            self::assertLessThanOrEqual(2, $unhealedWound->getValue());
            $woundSum += $unhealedWound->getValue();
            $collectedWounds[] = $unhealedWound;
        }
        $collectedWounds = $this->sortWoundsByValue($collectedWounds);
        $wounds = $this->sortWoundsByValue($health->getUnhealedWounds()->toArray());
        self::assertSame($wounds, $collectedWounds);
        self::assertSame(3, $woundSum);
        self::assertSame(15, $health->getRemainingHealthAmount());
        self::assertSame(3, $health->getUnhealedOrdinaryWoundsSum());
        self::assertSame(-3, $health->getMalusCausedByWounds($this->createWill(1), $this->createRoll2d6Plus(2)));
        self::assertTrue($health->isAlive());
        self::assertTrue($health->isConscious());

        self::assertSame(1, $health->healOrdinaryWoundsUpTo(1));
        self::assertSame(16, $health->getRemainingHealthAmount());
        self::assertSame(2, $health->getUnhealedOrdinaryWoundsSum());
        self::assertSame(0, $health->getUnhealedSeriousWoundsSum());
        self::assertSame(0, $health->getNumberOfSeriousInjuries());
        self::assertSame(0, $health->getMalusCausedByWounds($this->createWill(), $this->createRoll2d6Plus()));
        self::assertTrue($health->isAlive());
        self::assertTrue($health->isConscious());

        self::assertSame(2, $health->healSeriousAndOrdinaryWoundsUpTo(999));
        self::assertSame(18, $health->getRemainingHealthAmount());
        self::assertCount(0, $health->getUnhealedWounds());
        self::assertSame(0, $health->getUnhealedOrdinaryWoundsSum());
        self::assertSame(0, $health->getUnhealedSeriousWoundsSum());
        self::assertSame(0, $health->getNumberOfSeriousInjuries());
        self::assertSame(0, $health->getMalusCausedByWounds($this->createWill(), $this->createRoll2d6Plus()));
        self::assertTrue($health->isAlive());
        self::assertTrue($health->isConscious());
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

    private function sortWoundsByValue(array $wounds)
    {
        usort($wounds, function (Wound $wound1, Wound $wound2) {
            if ($wound1->getValue() < $wound2->getValue()) {
                return -1;
            }
            if ($wound1->getValue() === $wound2->getValue()) {
                return 0;
            }

            return 1;
        });

        return $wounds;
    }

    /**
     * @return \Mockery\MockInterface|WoundOrigin
     */
    private function createWoundOrigin()
    {
        return $this->mockery(WoundOrigin::class);
    }

    /**
     * @return \Mockery\MockInterface|AfflictionByWound
     */
    private function createAffliction()
    {
        return $this->mockery(AfflictionByWound::class);
    }

    /**
     * @test
     * @dataProvider provideWoundLimitAndHalfOrMoreWoundRow
     * @expectedException \DrdPlus\Person\Health\Exceptions\GivenWoundSizeShouldBeSeriousInjury
     * @param $woundLimit
     * @param $woundValue
     */
    public function I_can_not_create_ordinary_wound_same_or_greater_than_half_of_wound_row($woundLimit, $woundValue)
    {
        $health = new Health($this->createWoundsLimit($woundLimit));
        $health->createWound(WoundSize::createIt($woundValue), $this->createWill(), $this->createRoll2d6Plus());
    }

    public function provideWoundLimitAndHalfOrMoreWoundRow()
    {
        return [
            [2, 1],
            [3, 2],
            [90, 45],
            [90, 46],
            [90, 269],
        ];
    }
}
