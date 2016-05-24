<?php
namespace DrdPlus\Tests\Person\Health;

use Drd\DiceRoll\Templates\Rollers\Roller2d6DrdPlus;
use Drd\DiceRoll\Templates\Rollers\SpecificRolls\Roll2d6DrdPlus;
use DrdPlus\Person\Health\Afflictions\AfflictionByWound;
use DrdPlus\Person\Health\GridOfWounds;
use DrdPlus\Person\Health\HealingPower;
use DrdPlus\Person\Health\Health;
use DrdPlus\Person\Health\OrdinaryWoundOrigin;
use DrdPlus\Person\Health\SpecificWoundOrigin;
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
        self::assertSame(0, $health->getNewOrdinaryWoundsSum());
        self::assertSame(0, $health->getUnhealedSeriousWoundsSum());
        self::assertCount(0, $health->getAfflictions());
        self::assertSame(369, $health->getRemainingHealthAmount());
        self::assertSame(369, $health->getHealthMaximum());
        self::assertSame(0, $health->getMalusCausedByWounds());
        self::assertSame(0, $health->getNumberOfSeriousInjuries());
        self::assertCount(0, $health->getPains());
        self::assertSame(0, $health->getSignificantMalus());
        self::assertTrue($health->isAlive());
        self::assertTrue($health->isConscious());

        self::assertInstanceOf(TreatmentBoundary::class, $health->getTreatmentBoundary());
        self::assertSame(0, $health->getTreatmentBoundary()->getValue());

        self::assertInstanceOf(GridOfWounds::class, $health->getGridOfWounds());
        self::assertSame($health->getUnhealedWoundsSum(), $health->getGridOfWounds()->getSumOfWounds());
        self::assertSame($woundsLimitValue, $health->getGridOfWounds()->getWoundsPerRowMaximum());
        self::assertSame($health->getGridOfWounds()->getWoundsPerRowMaximum() * 3, $health->getHealthMaximum());
        self::assertSame(
            $health->getGridOfWounds()->getWoundsPerRowMaximum() * 3 - $health->getGridOfWounds()->getSumOfWounds(),
            $health->getRemainingHealthAmount(),
            'Expected different amount of reaming health'
        );
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
     * @param $healUpTo
     * @param $expectedHealedAmount
     * @return \Mockery\MockInterface|HealingPower
     */
    private function createHealingPower($healUpTo, $expectedHealedAmount)
    {
        $healingPower = $this->mockery(HealingPower::class);
        $healingPower->shouldReceive('getHealUpTo')
            ->andReturn($healUpTo);
        $healingPower->shouldReceive('decreaseByHealedAmount')
            ->with($expectedHealedAmount)
            ->andReturn($decreasedHealingPower = $this->mockery(HealingPower::class));
        $decreasedHealingPower->shouldReceive('getHealUpTo')
            ->andReturn(0);

        return $healingPower;
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
    private function createRoller2d6Plus($value = null)
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
        self::assertSame(0, $health->getMalusCausedByWounds());
        self::assertTrue($health->isAlive());
        self::assertTrue($health->isConscious());

        $wound = $health->createWound(
            $this->createWoundSize(1),
            $woundOrigin = SpecificWoundOrigin::getElementalWoundOrigin(),
            $this->createWill(),
            $this->createRoller2d6Plus()
        );
        self::assertInstanceOf(Wound::class, $wound);
        self::assertSame(1, $wound->getValue());
        self::assertNotEquals(
            $woundOrigin,
            $wound->getWoundOrigin(),
            'The specific wound origin should be replaced by generic on such small wound'
        );
        self::assertSame(OrdinaryWoundOrigin::getIt(), $wound->getWoundOrigin());
        self::assertCount(1, $health->getUnhealedWounds());
        self::assertSame($wound, $health->getUnhealedWounds()->current());
        self::assertSame(14, $health->getRemainingHealthAmount());
        self::assertSame(1, $health->getNewOrdinaryWoundsSum());
        self::assertSame(0, $health->getMalusCausedByWounds());
        self::assertSame(0, $health->getTreatmentBoundary()->getValue());
        self::assertTrue($health->isAlive());
        self::assertTrue($health->isConscious());

        $wound = $health->createWound(
            $this->createWoundSize(2),
            $woundOrigin = SpecificWoundOrigin::getMechanicalCutWoundOrigin(),
            $this->createWill(1),
            $this->createRoller2d6Plus(2)
        );
        self::assertInstanceOf(Wound::class, $wound);
        self::assertSame(2, $wound->getValue());
        self::assertNotEquals(
            $woundOrigin,
            $wound->getWoundOrigin(),
            'The specific wound origin should be replaced by generic on such small wound'
        );
        self::assertSame(OrdinaryWoundOrigin::getIt(), $wound->getWoundOrigin());
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
        self::assertSame(3, $health->getNewOrdinaryWoundsSum());
        self::assertSame(0, $health->getMalusCausedByWounds());
        self::assertSame(0, $health->getTreatmentBoundary()->getValue());
        self::assertTrue($health->isAlive());
        self::assertTrue($health->isConscious());

        $health->createWound(
            $this->createWoundSize(2),
            $woundOrigin = SpecificWoundOrigin::getMechanicalCutWoundOrigin(),
            $this->createWill(1),
            $this->createRoller2d6Plus(2)
        );
        self::assertCount(3, $health->getUnhealedWounds());
        $woundSum = 0;
        $collectedWounds = [];
        foreach ($health->getUnhealedWounds() as $unhealedWound) {
            self::assertInstanceOf(Wound::class, $unhealedWound);
            self::assertLessThanOrEqual(2, $unhealedWound->getValue());
            $woundSum += $unhealedWound->getValue();
            $collectedWounds[] = $unhealedWound;
            self::assertNotEquals(
                $woundOrigin,
                $unhealedWound->getWoundOrigin(),
                'The specific wound origin should be replaced by generic on such small wound'
            );
            self::assertSame(OrdinaryWoundOrigin::getIt(), $unhealedWound->getWoundOrigin());
        }
        $collectedWounds = $this->sortWoundsByValue($collectedWounds);
        $wounds = $this->sortWoundsByValue($health->getUnhealedWounds()->toArray());
        self::assertSame($wounds, $collectedWounds);
        self::assertSame(5, $woundSum);
        self::assertSame(10, $health->getRemainingHealthAmount());
        self::assertSame(5, $health->getNewOrdinaryWoundsSum());
        self::assertSame(-3, $health->getMalusCausedByWounds());
        self::assertSame(0, $health->getTreatmentBoundary()->getValue());
        self::assertTrue($health->isAlive());
        self::assertTrue($health->isConscious());

        self::assertSame(1, $health->healNewOrdinaryWoundsUpTo($this->createHealingPower(1, 1), $this->createWill(), $this->createRoller2d6Plus()));
        self::assertSame(11, $health->getRemainingHealthAmount());
        self::assertSame(4, $health->getUnhealedWoundsSum());
        self::assertSame(0, $health->getNewOrdinaryWoundsSum(), 'All ordinary wounds should become "old" after heal');
        self::assertSame(0, $health->getUnhealedSeriousWoundsSum());
        self::assertSame(0, $health->getNumberOfSeriousInjuries());
        self::assertSame(0, $health->getMalusCausedByWounds());
        self::assertSame(4, $health->getTreatmentBoundary()->getValue());
        self::assertTrue($health->isAlive());
        self::assertTrue($health->isConscious());

        self::assertSame(
            0,
            $health->healNewOrdinaryWoundsUpTo($this->createHealingPower(1, 0), $this->createWill(), $this->createRoller2d6Plus()),
            'Nothing should be healed because of treatment boundary'
        );
        self::assertSame(11, $health->getRemainingHealthAmount());
        self::assertSame(4, $health->getUnhealedWoundsSum());
        self::assertSame(0, $health->getNewOrdinaryWoundsSum());
        self::assertSame(0, $health->getUnhealedSeriousWoundsSum());
        self::assertSame(0, $health->getNumberOfSeriousInjuries());
        self::assertSame(0, $health->getMalusCausedByWounds());
        self::assertSame(4, $health->getTreatmentBoundary()->getValue());
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
        self::assertSame(0, $health->getMalusCausedByWounds());
        self::assertTrue($health->isAlive());
        self::assertTrue($health->isConscious());

        $seriousWoundByStab = $health->createWound(
            $this->createWoundSize(3),
            $specificWoundOrigin = SpecificWoundOrigin::getMechanicalStabWoundOrigin(),
            $this->createWill(),
            $this->createRoller2d6Plus()
        );
        self::assertInstanceOf(Wound::class, $seriousWoundByStab);
        self::assertSame(3, $seriousWoundByStab->getValue());
        self::assertSame($specificWoundOrigin, $seriousWoundByStab->getWoundOrigin());
        self::assertCount(1, $health->getUnhealedWounds());
        self::assertSame($seriousWoundByStab, $health->getUnhealedWounds()->current());
        self::assertSame(15, $health->getRemainingHealthAmount());
        self::assertSame(0, $health->getNewOrdinaryWoundsSum());
        self::assertSame(3, $health->getUnhealedSeriousWoundsSum());
        self::assertSame(0, $health->getMalusCausedByWounds(), 'There are not enough wounds to suffer from them yet.');
        self::assertSame(3, $health->getTreatmentBoundary()->getValue());
        self::assertTrue($health->isAlive());
        self::assertTrue($health->isConscious());

        $health->addAffliction($affliction = $this->createAffliction($seriousWoundByStab));
        self::assertCount(1, $health->getAfflictions());
        self::assertSame($affliction, $health->getAfflictions()->current());
        unset($seriousWoundByStab);

        $seriousWoundByPsyche = $health->createWound(
            $this->createWoundSize(5),
            $specificWoundOrigin = SpecificWoundOrigin::getPsychicalWoundOrigin(),
            $this->createWill(-10),
            $this->createRoller2d6Plus(2)
        );
        self::assertInstanceOf(Wound::class, $seriousWoundByPsyche);
        self::assertSame(5, $seriousWoundByPsyche->getValue());
        self::assertTrue($seriousWoundByPsyche->isSerious());
        self::assertSame($specificWoundOrigin, $seriousWoundByPsyche->getWoundOrigin());
        self::assertCount(2, $health->getUnhealedWounds());
        $woundSum = 0;
        $collectedWounds = [];
        foreach ($health->getUnhealedWounds() as $unhealedWound) {
            self::assertInstanceOf(Wound::class, $unhealedWound);
            self::assertLessThanOrEqual(5, $unhealedWound->getValue());
            $woundSum += $unhealedWound->getValue();
            $collectedWounds[] = $unhealedWound;
        }
        $collectedWounds = $this->sortWoundsByValue($collectedWounds);
        $unhealedWounds = $this->sortWoundsByValue($health->getUnhealedWounds()->toArray());
        self::assertSame($unhealedWounds, $collectedWounds);
        self::assertCount(2, $health->getUnhealedWounds());
        self::assertSame(8, $woundSum);
        self::assertSame(0, $health->getNewOrdinaryWoundsSum());
        self::assertSame(8, $health->getUnhealedSeriousWoundsSum());
        self::assertSame(8, $health->getUnhealedWoundsSum());
        self::assertSame(10, $health->getRemainingHealthAmount());
        self::assertSame(-3, $health->getMalusCausedByWounds());
        self::assertSame(8, $health->getTreatmentBoundary()->getValue());
        self::assertTrue($health->isAlive());
        self::assertTrue($health->isConscious());

        self::assertSame(0, $health->healNewOrdinaryWoundsUpTo(
            $this->createHealingPower(1, 0),
            $this->createWill(),
            $this->createRoller2d6Plus())
        );
        self::assertSame(8, $health->getUnhealedWoundsSum());
        self::assertCount(2, $health->getUnhealedWounds());
        self::assertSame(8, $health->getTreatmentBoundary()->getValue());
        self::assertSame(10, $health->getRemainingHealthAmount());

        self::assertSame(3, $health->healSeriousWound(
            $seriousWoundByPsyche,
            $this->createHealingPower(3, 3),
            $this->createWill(),
            $this->createRoller2d6Plus())
        );
        self::assertSame(13, $health->getRemainingHealthAmount());
        self::assertCount(2, $health->getUnhealedWounds());
        self::assertSame(5, $health->getUnhealedWoundsSum());
        self::assertSame(0, $health->getNewOrdinaryWoundsSum());
        self::assertSame(5, $health->getUnhealedSeriousWoundsSum());
        self::assertSame(2, $health->getNumberOfSeriousInjuries());
        self::assertSame(0, $health->getMalusCausedByWounds(), 'Malus should be gone because of low damage after heal');
        self::assertSame(5, $health->getTreatmentBoundary()->getValue());
        self::assertTrue($health->isAlive());
        self::assertTrue($health->isConscious());

        $smallScratch = $health->createWound(
            $this->createWoundSize(1), // just a small scratch to test immovable treatment boundary for ordinary wounds
            $specificWoundOrigin = SpecificWoundOrigin::getPsychicalWoundOrigin(),
            $this->createWill(-10),
            $this->createRoller2d6Plus(2)
        );
        self::assertFalse($smallScratch->isSerious());
        self::assertSame(5, $health->getTreatmentBoundary()->getValue());
        self::assertSame(-3, $health->getMalusCausedByWounds(), 'Even such scratch should cause malus because of filled row of wounds and low roll against');
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
     * @param Wound $wound
     * @return \Mockery\MockInterface|AfflictionByWound
     */
    private function createAffliction(Wound $wound)
    {
        $affliction = $this->mockery(AfflictionByWound::class);
        $affliction->shouldReceive('getWound')
            ->andReturn($wound);
        $affliction->shouldReceive('getName')
            ->andReturn('some terrible affliction');

        return $affliction;
    }

    /**
     * @test
     */
    public function I_can_not_lower_malus_on_new_wound_by_better_roll()
    {
        $health = new Health($this->createWoundsLimit(5));
        self::assertSame(0, $health->getMalusCausedByWounds());

        $health->createWound(
            $this->createWoundSize(5),
            SpecificWoundOrigin::getElementalWoundOrigin(),
            $this->createWill(5),
            $this->createRoller2d6Plus(5)
        );
        self::assertSame(-1, $health->getMalusCausedByWounds());

        $health->createWound(
            $this->createWoundSize(1),
            SpecificWoundOrigin::getElementalWoundOrigin(),
            $this->createWill(5),
            $this->createRoller2d6Plus(40)
        );
        self::assertSame(-1, $health->getMalusCausedByWounds());
    }

    /**
     * @test
     */
    public function I_can_not_increase_malus_on_new_wound_by_worse_roll()
    {
        $health = new Health($this->createWoundsLimit(5));
        self::assertSame(0, $health->getMalusCausedByWounds());

        // 3 ordinary wounds
        $health->createWound(
            $this->createWoundSize(2),
            SpecificWoundOrigin::getElementalWoundOrigin(),
            $this->createWill(5),
            $this->createRoller2d6Plus(5)
        );
        $health->createWound(
            $this->createWoundSize(2),
            SpecificWoundOrigin::getElementalWoundOrigin(),
            $this->createWill(5),
            $this->createRoller2d6Plus(5)
        );
        $health->createWound(
            $this->createWoundSize(2),
            SpecificWoundOrigin::getElementalWoundOrigin(),
            $this->createWill(5),
            $this->createRoller2d6Plus(5)
        );
        self::assertSame(-1, $health->getMalusCausedByWounds());

        self::assertSame(
            1,
            $health->healNewOrdinaryWoundsUpTo(
                $this->createHealingPower(1, 1),
                $this->createWill(5),
                $this->createRoller2d6Plus(-5)
            )
        );
        self::assertSame(-1, $health->getMalusCausedByWounds(), 'Malus should not be increased');
    }

    /**
     * @test
     * @expectedException \DrdPlus\Person\Health\Exceptions\UnknownAfflictionOriginatingWound
     */
    public function I_can_not_add_affliction_of_unknown_wound()
    {
        $health = new Health($this->createWoundsLimit(5));
        $affliction = $this->createAffliction($this->createWound());
        $health->addAffliction($affliction);
    }

    /**
     * @return \Mockery\MockInterface|Wound
     */
    private function createWound()
    {
        $wound = $this->mockery(Wound::class);
        $wound->shouldReceive('getHealth')
            ->andReturn($this->mockery(Health::class));
        $wound->shouldReceive('getWoundOrigin')
            ->andReturn(SpecificWoundOrigin::getMechanicalCrushWoundOrigin());
        $wound->shouldReceive('__toString')
            ->andReturn('123');

        return $wound;
    }

    /**
     * @test
     * @expectedException \DrdPlus\Person\Health\Exceptions\AfflictionIsAlreadyRegistered
     */
    public function I_can_not_add_same_affliction_twice()
    {
        $health = new Health($this->createWoundsLimit(5));
        $wound = $health->createWound(
            $this->createWoundSize(6),
            SpecificWoundOrigin::getElementalWoundOrigin(),
            $this->createWill(1),
            $this->createRoller2d6Plus(2)
        );
        $affliction = $this->createAffliction($wound);
        try {
            $health->addAffliction($affliction);
        } catch (\Exception $exception) {
            self::fail('No exception should happened so far: ' . $exception->getTraceAsString());
        }
        $health->addAffliction($affliction);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Person\Health\Exceptions\UnknownAfflictionOriginatingWound
     */
    public function I_can_not_add_affliction_with_to_health_unknown_wound()
    {
        $health = new Health($this->createWoundsLimit(5));
        $wound = new Wound($health, $this->createWoundSize(6), SpecificWoundOrigin::getElementalWoundOrigin());
        $health->addAffliction($this->createAffliction($wound));
    }
}