<?php
namespace DrdPlus\Tests\Person\Health;

use Drd\DiceRoll\Templates\Rollers\Roller2d6DrdPlus;
use Drd\DiceRoll\Templates\Rollers\SpecificRolls\Roll2d6DrdPlus;
use DrdPlus\Person\Health\Afflictions\AfflictionByWound;
use DrdPlus\Person\Health\GridOfWounds;
use DrdPlus\Person\Health\HealingPower;
use DrdPlus\Person\Health\Health;
use DrdPlus\Person\Health\OrdinaryWoundOrigin;
use DrdPlus\Person\Health\ReasonToRollAgainstMalus;
use DrdPlus\Person\Health\SeriousWound;
use DrdPlus\Person\Health\SpecificWoundOrigin;
use DrdPlus\Person\Health\TreatmentBoundary;
use DrdPlus\Person\Health\Wound;
use DrdPlus\Person\Health\WoundSize;
use DrdPlus\Properties\Base\Will;
use DrdPlus\Properties\Derived\WoundBoundary;
use DrdPlus\Tables\Measurements\Wounds\WoundsTable;
use Granam\Tests\Tools\TestWithMockery;

/** @noinspection LongInheritanceChainInspection */
class HealthTest extends TestWithMockery
{
    /**
     * @test
     */
    public function I_can_use_it()
    {
        $health = $this->createHealthToTest(123);

        self::assertSame(123, $health->getWoundBoundaryValue());
        self::assertSame(369, $health->getRemainingHealthAmount());
        self::assertSame(369, $health->getHealthMaximum());
    }

    /**
     * @param int $woundBoundaryValue
     * @return Health
     */
    private function createHealthToTest($woundBoundaryValue)
    {
        $health = new Health($woundBoundary = $this->createWoundBoundary($woundBoundaryValue));
        $this->assertUnwounded($health, $woundBoundary);

        return $health;
    }

    /**
     * @param $value
     * @return \Mockery\MockInterface|WoundBoundary
     */
    private function createWoundBoundary($value)
    {
        $wounds = $this->mockery(WoundBoundary::class);
        $wounds->shouldReceive('getValue')
            ->andReturn($value);

        return $wounds;
    }

    private function assertUnwounded(Health $health, WoundBoundary $woundBoundary)
    {
        self::assertNull($health->getId(), 'Not yet persisted health should not has filled ID (it is database responsibility in this case)');
        self::assertSame($woundBoundary->getValue(), $health->getWoundBoundaryValue());
        self::assertSame($health->getGridOfWounds()->getWoundsPerRowMaximum(), $health->getWoundBoundaryValue());
        self::assertSame($health->getGridOfWounds()->getWoundsPerRowMaximum() * 3, $health->getHealthMaximum());
        self::assertSame($health->getGridOfWounds()->getWoundsPerRowMaximum() * 3, $health->getRemainingHealthAmount());
        self::assertCount(0, $health->getUnhealedWounds());
        self::assertSame(0, $health->getUnhealedNewOrdinaryWoundsSum());
        self::assertSame(0, $health->getUnhealedSeriousWoundsSum());
        self::assertSame(0, $health->getNumberOfSeriousInjuries());
        self::assertCount(0, $health->getAfflictions());
        self::assertSame(0, $health->getSignificantMalus());
        self::assertCount(0, $health->getPains());
        self::assertTrue($health->isAlive());
        self::assertTrue($health->isConscious());
        self::assertFalse($health->needsToRollAgainstMalus());
        self::assertNull($health->getReasonToRollAgainstMalus());

        self::assertInstanceOf(TreatmentBoundary::class, $health->getTreatmentBoundary());
        self::assertSame(0, $health->getTreatmentBoundary()->getValue());

        self::assertInstanceOf(GridOfWounds::class, $health->getGridOfWounds());
        self::assertSame(0, $health->getGridOfWounds()->getSumOfWounds());
    }

    /**
     * @test
     * @dataProvider provideConsciousAndAlive
     * @param int $woundsLimit
     * @param int $wound
     * @param bool $isConscious
     * @param bool $isAlive
     */
    public function I_can_easily_find_out_if_person_is_conscious_and_alive($woundsLimit, $wound, $isConscious, $isAlive)
    {
        $health = $this->createHealthToTest($woundsLimit);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize($wound), SpecificWoundOrigin::getElementalWoundOrigin());

        self::assertSame($isConscious, $health->isConscious());
        self::assertSame($isAlive, $health->isAlive());
    }

    public function provideConsciousAndAlive()
    {
        return [
            [1, 0, true, true], // healthy
            [1, 1, true, true], // wounded
            [1, 2, false, true], // knocked down
            [1, 3, false, false], // dead
        ];
    }

    // TREATMENT BOUNDARY

    /**
     * @test
     */
    public function I_get_treatment_boundary_moved_to_reaming_wounds_on_ordinary_heal()
    {
        $health = $this->createHealthToTest(10);
        self::assertSame(0, $health->getTreatmentBoundary()->getValue());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(4), SpecificWoundOrigin::getMechanicalCutWoundOrigin());
        self::assertSame(0, $health->getTreatmentBoundary()->getValue());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->healNewOrdinaryWoundsUpTo($this->createHealingPower(1, 1));
        self::assertSame(3, $health->getTreatmentBoundary()->getValue());
        self::assertSame($health->getUnhealedWoundsSum(), $health->getTreatmentBoundary()->getValue());
    }

    /**
     * @test
     */
    public function I_get_treatment_boundary_increased_by_serious_wound_immediately()
    {
        $health = $this->createHealthToTest(10);
        self::assertSame(0, $health->getTreatmentBoundary()->getValue());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(7), SpecificWoundOrigin::getMechanicalCutWoundOrigin());
        self::assertSame(7, $health->getTreatmentBoundary()->getValue());
        self::assertSame($health->getUnhealedWoundsSum(), $health->getTreatmentBoundary()->getValue());
    }

    /**
     * @test
     */
    public function I_get_treatment_boundary_lowered_by_healed_serious_wound()
    {
        $health = $this->createHealthToTest(10);
        self::assertSame(0, $health->getTreatmentBoundary()->getValue());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $seriousWound = $health->createWound($this->createWoundSize(7), SpecificWoundOrigin::getMechanicalCutWoundOrigin());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->healSeriousWound($seriousWound, $this->createHealingPower(5));
        self::assertSame(2, $health->getTreatmentBoundary()->getValue());
    }

    /**
     * @test
     */
    public function I_do_not_have_lowered_treatment_boundary_by_healed_ordinary_wound()
    {
        $health = $this->createHealthToTest(10);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(3), SpecificWoundOrigin::getMechanicalCrushWoundOrigin());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(6), SpecificWoundOrigin::getMechanicalCutWoundOrigin());
        self::assertSame(6, $health->getTreatmentBoundary()->getValue());
        self::assertSame(9, $health->getUnhealedWoundsSum());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->healNewOrdinaryWoundsUpTo($this->createHealingPower(999, 3));
        self::assertSame(6, $health->getTreatmentBoundary()->getValue());
        self::assertSame(6, $health->getUnhealedWoundsSum());
    }

    /**
     * @test
     */
    public function I_get_treatment_boundary_lowered_by_regenerated_amount()
    {
        $health = $this->createHealthToTest(10);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(3), SpecificWoundOrigin::getMechanicalCrushWoundOrigin());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(6), SpecificWoundOrigin::getMechanicalCutWoundOrigin());
        self::assertSame(6, $health->getTreatmentBoundary()->getValue());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->regenerate(new HealingPower(8, new WoundsTable()));
        self::assertSame(
            1,
            $health->getTreatmentBoundary()->getValue(),
            'Both ordinary and serious wound should be regenerated, therefore treatment boundary should be moved by regenerating power'
        );
        self::assertSame(1, $health->getUnhealedWoundsSum());
    }

    // TODO getUnhealedOrdinaryWoundsValue should be same value as GridOfWounds()->getSumOfWounds() - TreatmentBoundary()->getValue()

    // ROLL ON MALUS RESULT

    /**
     * @test
     * @dataProvider provideDecreasingRollAgainstMalusData
     * @param $willValue
     * @param $rollValue
     * @param $expectedMalus
     */
    public function I_should_roll_against_malus_from_wounds_because_of_new_wound($willValue, $rollValue, $expectedMalus)
    {
        $health = $this->createHealthToTest(10);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(10), SpecificWoundOrigin::getElementalWoundOrigin());
        self::assertTrue($health->needsToRollAgainstMalus());
        self::assertSame(ReasonToRollAgainstMalus::getWoundReason(), $health->getReasonToRollAgainstMalus());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(
            $expectedMalus,
            $health->rollAgainstMalusFromWounds(
                $this->createWill($willValue),
                $this->createRoller2d6Plus($rollValue)
            )
        );
        self::assertFalse($health->needsToRollAgainstMalus());
        self::assertNull($health->getReasonToRollAgainstMalus());
    }

    public function provideDecreasingRollAgainstMalusData()
    {
        return [
            [7, 8, 0],
            [99, 99, 0],
            [6, 4, -1],
            [6, 8, -1],
            [3, 2, -2],
            [2, 3, -2],
            [1, 1, -3],
        ];
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

    /**
     * @test
     * @dataProvider provideIncreasingRollAgainstMalusData
     * @param $willValue
     * @param $rollValue
     * @param $expectedMalus
     */
    public function I_should_roll_against_malus_from_wounds_because_of_heal_of_ordinary_wound($willValue, $rollValue, $expectedMalus)
    {
        $health = $this->createHealthToTest(10);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(4), SpecificWoundOrigin::getElementalWoundOrigin());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(4), SpecificWoundOrigin::getElementalWoundOrigin());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(4), SpecificWoundOrigin::getElementalWoundOrigin());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->rollAgainstMalusFromWounds($this->createWill(-1), $this->createRoller2d6Plus(3)); // -3 malus as a result
        self::assertFalse($health->needsToRollAgainstMalus());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->healNewOrdinaryWoundsUpTo($this->createHealingPower(1, 1));
        self::assertSame(ReasonToRollAgainstMalus::getHealReason(), $health->getReasonToRollAgainstMalus());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(
            $expectedMalus,
            $health->rollAgainstMalusFromWounds(
                $this->createWill($willValue),
                $this->createRoller2d6Plus($rollValue)
            )
        );
        self::assertFalse($health->needsToRollAgainstMalus());
        self::assertNull($health->getReasonToRollAgainstMalus());
    }

    public function provideIncreasingRollAgainstMalusData()
    {
        return [
            [1, 1, -3],
            [3, 2, -2],
            [2, 3, -2],
            [6, 4, -1],
            [6, 8, -1],
            [7, 8, 0],
            [99, 99, 0],
        ];
    }

    /**
     * @test
     * @dataProvider provideIncreasingRollAgainstMalusData
     * @param $willValue
     * @param $rollValue
     * @param $expectedMalus
     */
    public function I_should_roll_against_malus_from_wounds_because_of_heal_of_serious_wound($willValue, $rollValue, $expectedMalus)
    {
        $health = $this->createHealthToTest(10);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $seriousWound = $health->createWound($this->createWoundSize(15), SpecificWoundOrigin::getElementalWoundOrigin());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->rollAgainstMalusFromWounds($this->createWill(-1), $this->createRoller2d6Plus(3)); // -3 malus as a result
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->healSeriousWound($seriousWound, $this->createHealingPower(1));
        self::assertSame(ReasonToRollAgainstMalus::getHealReason(), $health->getReasonToRollAgainstMalus());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(
            $expectedMalus,
            $health->rollAgainstMalusFromWounds(
                $this->createWill($willValue),
                $this->createRoller2d6Plus($rollValue)
            )
        );
        self::assertFalse($health->needsToRollAgainstMalus());
        self::assertNull($health->getReasonToRollAgainstMalus());
    }

    /**
     * @test
     * @dataProvider provideIncreasingRollAgainstMalusData
     * @param $willValue
     * @param $rollValue
     * @param $expectedMalus
     */
    public function I_should_roll_against_malus_from_wounds_because_of_regeneration($willValue, $rollValue, $expectedMalus)
    {
        $health = $this->createHealthToTest(10);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(15), SpecificWoundOrigin::getElementalWoundOrigin());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->rollAgainstMalusFromWounds($this->createWill(-1), $this->createRoller2d6Plus(3)); // -3 malus as a result
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->regenerate($this->createHealingPower(5, 5));
        self::assertSame(ReasonToRollAgainstMalus::getHealReason(), $health->getReasonToRollAgainstMalus());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(
            $expectedMalus,
            $health->rollAgainstMalusFromWounds(
                $this->createWill($willValue),
                $this->createRoller2d6Plus($rollValue)
            )
        );
        self::assertFalse($health->needsToRollAgainstMalus());
        self::assertNull($health->getReasonToRollAgainstMalus());
    }

    /**
     * @test
     * @dataProvider provideIncreasingRollAgainstMalusData
     * @param $willValue
     * @param $rollValue
     * @param $expectedMalus
     */
    public function I_should_roll_against_malus_from_wounds_because_of_increased_wound_boundary_like_heal($willValue, $rollValue, $expectedMalus)
    {
        $health = $this->createHealthToTest(10);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(15), SpecificWoundOrigin::getElementalWoundOrigin());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->rollAgainstMalusFromWounds($this->createWill(-1), $this->createRoller2d6Plus(3)); // -3 malus as a result
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->changeWoundBoundary($this->createWoundBoundary(11));
        self::assertSame(ReasonToRollAgainstMalus::getHealReason(), $health->getReasonToRollAgainstMalus());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(
            $expectedMalus,
            $health->rollAgainstMalusFromWounds(
                $this->createWill($willValue),
                $this->createRoller2d6Plus($rollValue)
            )
        );
        self::assertFalse($health->needsToRollAgainstMalus());
        self::assertNull($health->getReasonToRollAgainstMalus());
    }

    /**
     * @test
     * @dataProvider provideDecreasingRollAgainstMalusData
     * @param $willValue
     * @param $rollValue
     * @param $expectedMalus
     */
    public function I_should_roll_against_malus_from_wounds_because_of_decreased_wound_boundary_like_wound($willValue, $rollValue, $expectedMalus)
    {
        $health = $this->createHealthToTest(10);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(15), SpecificWoundOrigin::getElementalWoundOrigin());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->rollAgainstMalusFromWounds($this->createWill(5), $this->createRoller2d6Plus(10)); // 0 malus as a result
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->changeWoundBoundary($this->createWoundBoundary(9));
        self::assertSame(ReasonToRollAgainstMalus::getWoundReason(), $health->getReasonToRollAgainstMalus());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(
            $expectedMalus,
            $health->rollAgainstMalusFromWounds(
                $this->createWill($willValue),
                $this->createRoller2d6Plus($rollValue)
            )
        );
        self::assertFalse($health->needsToRollAgainstMalus());
        self::assertNull($health->getReasonToRollAgainstMalus());
    }

    // TODO malus can be only decreased

    /**
     * @test
     * @expectedException \DrdPlus\Person\Health\Exceptions\UselessRollAgainstMalus
     */
    public function I_can_not_roll_on_malus_from_wounds_if_not_needed()
    {
        $health = $this->createHealthToTest(10);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->rollAgainstMalusFromWounds($this->createWill(), $this->createRoller2d6Plus());
    }

    // ROLL ON MALUS EXPECTED

    /**
     * @test
     * @expectedException \DrdPlus\Person\Health\Exceptions\NeedsToRollAgainstMalusFirst
     */
    public function I_can_not_add_new_wound_if_roll_on_malus_expected()
    {
        $health = $this->createHealthToTest(10);
        try {
            $health->createWound($this->createWoundSize(10), SpecificWoundOrigin::getElementalWoundOrigin());
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getTraceAsString());
        }
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(10), SpecificWoundOrigin::getElementalWoundOrigin());
    }

    /**
     * @test
     * @expectedException \DrdPlus\Person\Health\Exceptions\NeedsToRollAgainstMalusFirst
     */
    public function I_can_not_heal_new_ordinary_wounds_if_roll_on_malus_expected()
    {
        $health = $this->createHealthToTest(10);
        try {
            $health->createWound($this->createWoundSize(4), SpecificWoundOrigin::getElementalWoundOrigin());
            $health->createWound($this->createWoundSize(4), SpecificWoundOrigin::getElementalWoundOrigin());
            $health->createWound($this->createWoundSize(4), SpecificWoundOrigin::getElementalWoundOrigin());
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getTraceAsString());
        }
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->healNewOrdinaryWoundsUpTo($this->createHealingPower(5));
    }

    /**
     * @test
     * @expectedException \DrdPlus\Person\Health\Exceptions\NeedsToRollAgainstMalusFirst
     */
    public function I_can_not_heal_serious_wound_if_roll_on_malus_expected()
    {
        $health = $this->createHealthToTest(10);
        try {
            $seriousWound = $health->createWound($this->createWoundSize(14), SpecificWoundOrigin::getElementalWoundOrigin());
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getTraceAsString());
        }
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        $health->healSeriousWound($seriousWound, $this->createHealingPower(5));
    }

    /**
     * @test
     * @expectedException \DrdPlus\Person\Health\Exceptions\NeedsToRollAgainstMalusFirst
     */
    public function I_can_not_regenerate_if_roll_on_malus_expected()
    {
        $health = $this->createHealthToTest(10);
        try {
            $health->createWound($this->createWoundSize(14), SpecificWoundOrigin::getElementalWoundOrigin());
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getTraceAsString());
        }
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->regenerate($this->createHealingPower(5));
    }

    /**
     * @test
     * @expectedException \DrdPlus\Person\Health\Exceptions\NeedsToRollAgainstMalusFirst
     */
    public function I_can_not_get_malus_from_wounds_if_roll_on_it_expected()
    {
        $health = $this->createHealthToTest(10);
        try {
            $health->createWound($this->createWoundSize(14), SpecificWoundOrigin::getElementalWoundOrigin());
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getTraceAsString());
        }
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->getSignificantMalus();
    }

    // AFFLICTION

    /**
     * @test
     */
    public function I_can_add_affliction()
    {
        $health = new Health($this->createWoundBoundary(5));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $wound = $health->createWound($this->createWoundSize(5), SpecificWoundOrigin::getMechanicalCrushWoundOrigin());
        $affliction = $this->createAffliction($wound);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->addAffliction($affliction);
        self::assertCount(1, $health->getAfflictions());
        self::assertSame($affliction, $health->getAfflictions()->current());
    }

    /**
     * @param Wound $wound
     * @return \Mockery\MockInterface|AfflictionByWound
     */
    private function createAffliction(Wound $wound)
    {
        $affliction = $this->mockery(AfflictionByWound::class);
        $affliction->shouldReceive('getSeriousWound')
            ->andReturn($wound);
        $affliction->shouldReceive('getName')
            ->andReturn('some terrible affliction');

        return $affliction;
    }

    /**
     * @test
     * @expectedException \DrdPlus\Person\Health\Exceptions\UnknownAfflictionOriginatingWound
     */
    public function I_can_not_add_affliction_of_unknown_wound()
    {
        $health = new Health($this->createWoundBoundary(5));
        $affliction = $this->createAffliction($this->createWound());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
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
        $health = new Health($this->createWoundBoundary(5));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $wound = $health->createWound(
            $this->createWoundSize(6),
            SpecificWoundOrigin::getElementalWoundOrigin()
        );
        $affliction = $this->createAffliction($wound);
        try {
            $health->addAffliction($affliction);
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getTraceAsString());
        }
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->addAffliction($affliction);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Person\Health\Exceptions\UnknownAfflictionOriginatingWound
     */
    public function I_can_not_add_affliction_with_to_health_unknown_wound()
    {
        $health = new Health($this->createWoundBoundary(5));
        $wound = new SeriousWound(
            $health,
            $this->createWoundSize(6),
            SpecificWoundOrigin::getElementalWoundOrigin()
        );
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->addAffliction($this->createAffliction($wound));
    }

    // NEW WOUND

    /**
     * @test
     */
    public function I_can_be_ordinary_wounded()
    {
        $health = $this->createHealthToTest(5);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $ordinaryWound = $health->createWound(
            $this->createWoundSize(2),
            SpecificWoundOrigin::getElementalWoundOrigin()
        );
        self::assertInstanceOf(Wound::class, $ordinaryWound);
        self::assertSame(2, $ordinaryWound->getValue());
        self::assertSame(
            OrdinaryWoundOrigin::getIt(),
            $ordinaryWound->getWoundOrigin(),
            'The ordinary wound origin should be used on such small wound'
        );
        self::assertCount(1, $health->getUnhealedWounds());
        self::assertSame($ordinaryWound, $health->getUnhealedWounds()->last());
        self::assertSame(13, $health->getRemainingHealthAmount());
        self::assertSame(2, $health->getUnhealedNewOrdinaryWoundsSum());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(0, $health->getSignificantMalus());
        self::assertFalse($health->needsToRollAgainstMalus());
        self::assertNull($health->getReasonToRollAgainstMalus());

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $anotherOrdinaryWound = $health->createWound(
            $this->createWoundSize(1),
            SpecificWoundOrigin::getElementalWoundOrigin()
        );
        self::assertInstanceOf(Wound::class, $anotherOrdinaryWound);
        self::assertSame(1, $anotherOrdinaryWound->getValue());
        self::assertSame(
            OrdinaryWoundOrigin::getIt(),
            $anotherOrdinaryWound->getWoundOrigin(),
            'The ordinary wound origin should be used on such small wound'
        );
        self::assertCount(2, $health->getUnhealedWounds());
        self::assertSame($anotherOrdinaryWound, $health->getUnhealedWounds()->last());
        self::assertSame([$ordinaryWound, $anotherOrdinaryWound], $health->getUnhealedWounds()->toArray());
        self::assertSame(3, $health->getUnhealedNewOrdinaryWoundsSum());
        self::assertSame(12, $health->getRemainingHealthAmount());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(0, $health->getSignificantMalus());
        self::assertFalse($health->needsToRollAgainstMalus());
        self::assertNull($health->getReasonToRollAgainstMalus());
    }

    /**
     * @test
     */
    public function I_can_be_ordinary_healed()
    {
        $health = $this->createHealthToTest(7);

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(1), SpecificWoundOrigin::getElementalWoundOrigin());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(3), SpecificWoundOrigin::getMechanicalCrushWoundOrigin());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(2), SpecificWoundOrigin::getMechanicalStabWoundOrigin());

        self::assertSame(15, $health->getRemainingHealthAmount());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(4, $health->healNewOrdinaryWoundsUpTo(new HealingPower(4, new WoundsTable())));
        self::assertSame(19, $health->getRemainingHealthAmount());
        self::assertSame(2, $health->getUnhealedWoundsSum());
        self::assertSame(0, $health->getUnhealedNewOrdinaryWoundsSum(), 'All ordinary wounds should become "old" after heal');
        self::assertSame(0, $health->getUnhealedSeriousWoundsSum());
        self::assertSame(0, $health->getNumberOfSeriousInjuries());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(0, $health->getSignificantMalus());
        self::assertTrue($health->needsToRollAgainstMalus());
        self::assertNull($health->getReasonToRollAgainstMalus());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(
            0,
            $health->healNewOrdinaryWoundsUpTo($this->createHealingPower(10, 0)),
            'Nothing should be healed as a "new ordinary wound: because of treatment boundary'
        );
        self::assertSame(19, $health->getRemainingHealthAmount());
        self::assertSame(2, $health->getUnhealedWoundsSum());
    }

    /**
     * @param $healUpTo
     * @param $expectedHealedAmount
     * @return \Mockery\MockInterface|HealingPower
     */
    private function createHealingPower($healUpTo = null, $expectedHealedAmount = null)
    {
        $healingPower = $this->mockery(HealingPower::class);
        if ($healUpTo !== null) {
            $healingPower->shouldReceive('getHealUpTo')
                ->andReturn($healUpTo);
        }
        if ($expectedHealedAmount !== null) {
            $healingPower->shouldReceive('decreaseByHealedAmount')
                ->with($expectedHealedAmount)
                ->andReturn($decreasedHealingPower = $this->mockery(HealingPower::class));
            $decreasedHealingPower->shouldReceive('getHealUpTo')
                ->andReturn(0);
        }

        return $healingPower;
    }

    /**
     * @test
     */
    public function I_can_be_seriously_wounded()
    {
        $health = $this->createHealthToTest(6);

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $seriousWoundByStab = $health->createWound(
            $this->createWoundSize(3),
            $specificWoundOrigin = SpecificWoundOrigin::getMechanicalStabWoundOrigin()
        );
        self::assertInstanceOf(Wound::class, $seriousWoundByStab);
        self::assertSame(3, $seriousWoundByStab->getValue());
        self::assertSame($specificWoundOrigin, $seriousWoundByStab->getWoundOrigin());
        self::assertCount(1, $health->getUnhealedWounds());
        self::assertSame($seriousWoundByStab, $health->getUnhealedWounds()->last());
        self::assertSame(15, $health->getRemainingHealthAmount());
        self::assertSame(0, $health->getUnhealedNewOrdinaryWoundsSum());
        self::assertSame(3, $health->getUnhealedSeriousWoundsSum());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(0, $health->getSignificantMalus(), 'There are not enough wounds to suffer from them yet.');
        self::assertFalse($health->needsToRollAgainstMalus());
        self::assertNull($health->getReasonToRollAgainstMalus());

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $seriousWoundByPsyche = $health->createWound(
            $this->createWoundSize(5),
            $specificWoundOrigin = SpecificWoundOrigin::getPsychicalWoundOrigin()
        );
        self::assertInstanceOf(Wound::class, $seriousWoundByPsyche);
        self::assertSame(5, $seriousWoundByPsyche->getValue());
        self::assertTrue($seriousWoundByPsyche->isSerious());
        self::assertSame($specificWoundOrigin, $seriousWoundByPsyche->getWoundOrigin());
        self::assertCount(2, $health->getUnhealedWounds());
        self::assertSame(0, $health->getUnhealedNewOrdinaryWoundsSum());
        self::assertSame(8, $health->getUnhealedSeriousWoundsSum());
        self::assertSame(8, $health->getUnhealedWoundsSum());
        self::assertSame(10, $health->getRemainingHealthAmount());
        self::assertTrue($health->needsToRollAgainstMalus());
        self::assertSame(ReasonToRollAgainstMalus::getWoundReason(), $health->getReasonToRollAgainstMalus());
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
    }

    /**
     * @test
     */
    public function I_can_be_seriously_healed()
    {
        $health = $this->createHealthToTest(6);

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $seriousWoundByStab = $health->createWound(
            $this->createWoundSize(3),
            $specificWoundOrigin = SpecificWoundOrigin::getMechanicalStabWoundOrigin()
        );
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $seriousWoundByPsyche = $health->createWound(
            $this->createWoundSize(5),
            $specificWoundOrigin = SpecificWoundOrigin::getPsychicalWoundOrigin()
        );

        self::assertSame(-3, $health->rollAgainstMalusFromWounds($this->createWill(-1), $this->createRoller2d6Plus(1)));
        self::assertSame(
            0,
            $health->healNewOrdinaryWoundsUpTo($this->createHealingPower(1, 0)),
            'Nothing should be healed because there is no ordinary wound'
        );
        self::assertSame(8, $health->getUnhealedWoundsSum());
        self::assertCount(2, $health->getUnhealedWounds());
        self::assertSame(10, $health->getRemainingHealthAmount());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(3, $health->healSeriousWound($seriousWoundByPsyche, $this->createHealingPower(3, 3)));
        self::assertSame(13, $health->getRemainingHealthAmount());
        self::assertCount(2, $health->getUnhealedWounds());
        self::assertSame(5, $health->getUnhealedWoundsSum());
        self::assertSame(0, $health->getUnhealedNewOrdinaryWoundsSum());
        self::assertSame(5, $health->getUnhealedSeriousWoundsSum());
        self::assertSame(2, $health->getNumberOfSeriousInjuries(), 'Both serious wounds are still unhealed');
        self::assertSame(0, $health->getSignificantMalus(), 'Malus should be gone because of low damage after heal');

        self::assertSame(3, $health->healSeriousWound($seriousWoundByStab, $this->createHealingPower(10, 3)));
        self::assertSame(16, $health->getRemainingHealthAmount());
        self::assertCount(1, $health->getUnhealedWounds());
        self::assertSame(2, $health->getUnhealedWoundsSum());
        self::assertSame(0, $health->getUnhealedNewOrdinaryWoundsSum());
        self::assertSame(2, $health->getUnhealedSeriousWoundsSum());
        self::assertSame(1, $health->getNumberOfSeriousInjuries(), 'Single serious wound is unhealed');
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
     * @test
     * @expectedException \DrdPlus\Person\Health\Exceptions\UnknownSeriousWoundToHeal
     */
    public function I_can_not_heal_serious_wound_from_different_health()
    {
        $seriousWound = new SeriousWound(new Health($this->createWoundBoundary(5)), $this->createWoundSize(5), SpecificWoundOrigin::getMechanicalCutWoundOrigin());
        $anotherHealth = new Health($this->createWoundBoundary(3));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $anotherHealth->healSeriousWound($seriousWound, $this->createHealingPower());
    }

    /**
     * @test
     * @expectedException \DrdPlus\Person\Health\Exceptions\UnknownSeriousWoundToHeal
     */
    public function I_can_not_heal_serious_wound_not_created_by_current_health()
    {
        $seriousWound = new SeriousWound($health = new Health($this->createWoundBoundary(5)), $this->createWoundSize(5), SpecificWoundOrigin::getMechanicalCutWoundOrigin());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->healSeriousWound($seriousWound, $this->createHealingPower());
    }

    /**
     * @test
     * @expectedException \DrdPlus\Person\Health\Exceptions\ExpectedFreshWoundToHeal
     */
    public function I_can_not_heal_old_serious_wound()
    {
        $health = new Health($this->createWoundBoundary(5));
        $seriousWound = $health->createWound(
            $this->createWoundSize(5),
            SpecificWoundOrigin::getMechanicalCutWoundOrigin()
//            $this->createWill(1),
//            $this->createRoller2d6Plus(4)
        );
        self::assertTrue($seriousWound->isSerious());
        $seriousWound->setOld();
        self::assertTrue($seriousWound->isOld());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->healSeriousWound($seriousWound, $this->createHealingPower());
    }

    // TODO simplify following tests

    /**
     * @test
     */
    public function Malus_is_not_lowered_on_new_wound_by_better_roll()
    {
        $health = new Health($this->createWoundBoundary(5));
        self::assertSame(0, $health->getSignificantMalus());

        $health->createWound(
            $this->createWoundSize(5),
            SpecificWoundOrigin::getElementalWoundOrigin()
//            $this->createWill(5),
//            $this->createRoller2d6Plus(5)
        );
        self::assertSame(-1, $health->getSignificantMalus());

        $health->createWound(
            $this->createWoundSize(1),
            SpecificWoundOrigin::getElementalWoundOrigin()
//            $this->createWill(5),
//            $this->createRoller2d6Plus(40)
        );
        self::assertSame(-1, $health->getSignificantMalus());
    }

    /**
     * @test
     */
    public function Malus_is_not_increased_on_new_heal_by_worse_roll()
    {
        $health = new Health($this->createWoundBoundary(5));
        self::assertSame(0, $health->getSignificantMalus());

        // 3 ordinary wounds
        $health->createWound(
            $this->createWoundSize(2),
            SpecificWoundOrigin::getElementalWoundOrigin()
//            $this->createWill(5),
//            $this->createRoller2d6Plus(5)
        );
        $health->createWound(
            $this->createWoundSize(2),
            SpecificWoundOrigin::getElementalWoundOrigin()
//            $this->createWill(5),
//            $this->createRoller2d6Plus(5)
        );
        $health->createWound(
            $this->createWoundSize(2),
            SpecificWoundOrigin::getElementalWoundOrigin()
//            $this->createWill(5),
//            $this->createRoller2d6Plus(5)
        );
        self::assertSame(-1, $health->getSignificantMalus());

        self::assertSame(
            1,
            $health->healNewOrdinaryWoundsUpTo(
                $this->createHealingPower(1, 1)
//                $this->createWill(5),
//                $this->createRoller2d6Plus(-5)
            )
        );
        self::assertSame(-1, $health->getSignificantMalus(), 'Malus should not be increased');
    }

}