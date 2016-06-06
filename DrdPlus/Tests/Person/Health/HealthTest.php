<?php
namespace DrdPlus\Tests\Person\Health;

use Drd\DiceRoll\Templates\Rollers\Roller2d6DrdPlus;
use Drd\DiceRoll\Templates\Rollers\SpecificRolls\Roll2d6DrdPlus;
use DrdPlus\Person\Health\Afflictions\AfflictionByWound;
use DrdPlus\Person\Health\Afflictions\SpecificAfflictions\Pain;
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

    // MALUS CONDITIONAL CHANGES

    /**
     * @test
     * @dataProvider provideRollForMalus
     * @param $willValue
     * @param $rollValue
     * @param $expectedMalus
     */
    public function Malus_can_increase_on_new_wound($willValue, $rollValue, $expectedMalus)
    {
        $health = $this->createHealthToTest(5);

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(5), SpecificWoundOrigin::getElementalWoundOrigin());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame($expectedMalus, $health->rollAgainstMalusFromWounds($this->createWill($willValue), $this->createRoller2d6Plus($rollValue)));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame($expectedMalus, $health->getSignificantMalus());

        for ($currentWillValue = $willValue, $currentRollValue = $rollValue;
            $currentRollValue > -2 && $currentWillValue > -2;
            $currentRollValue--, $currentWillValue--
        ) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $seriousWound = $health->createWound($this->createWoundSize(3), SpecificWoundOrigin::getElementalWoundOrigin());
            $currentlyExpectedMalus = max(0, min(3, (int)floor(($currentWillValue + $currentRollValue) / 5))) - 3; // 0; -1; -2; -3
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            self::assertSame(
                $currentlyExpectedMalus, // malus can increase (be more negative)
                $health->rollAgainstMalusFromWounds($this->createWill($currentWillValue), $this->createRoller2d6Plus($currentRollValue)),
                "For will $currentWillValue and roll $currentRollValue has been expected malus $currentlyExpectedMalus"
            );
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            self::assertSame($currentlyExpectedMalus, $health->getSignificantMalus());
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $health->healSeriousWound($seriousWound, $this->createHealingPower(5, 3)); // "resetting" currently given wound
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            // low values to ensure untouched malus (should not be increased, therefore changed here at all, on heal)
            $health->rollAgainstMalusFromWounds($this->createWill(-1), $this->createRoller2d6Plus(-1));
        }
    }

    public function provideRollForMalus()
    {
        return [
            [1, 1, -3],
            [-5, -5, -3],
            [10, 5, 0],
            [15, 0, 0],
            [13, 1, -1],
            [2, 7, -2],
            [3, 7, -1],
            [3, 1, -3],
            [3, 2, -2],
        ];
    }

    /**
     * @test
     * @dataProvider provideRollForMalus
     * @param $willValue
     * @param $rollValue
     * @param $expectedMalus
     */
    public function Malus_can_not_decrease_on_new_wound($willValue, $rollValue, $expectedMalus)
    {
        $health = $this->createHealthToTest(5);

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(5), SpecificWoundOrigin::getElementalWoundOrigin());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame($expectedMalus, $health->rollAgainstMalusFromWounds($this->createWill($willValue), $this->createRoller2d6Plus($rollValue)));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame($expectedMalus, $health->getSignificantMalus());

        for ($currentWillValue = $willValue, $currentRollValue = $rollValue;
            $currentRollValue < 16 && $currentWillValue < 10;
            $currentRollValue++, $currentWillValue++
        ) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $seriousWound = $health->createWound($this->createWoundSize(3), SpecificWoundOrigin::getElementalWoundOrigin());
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            self::assertSame(
                $expectedMalus, // malus should not be decreased (be closer to zero)
                $health->rollAgainstMalusFromWounds($this->createWill($currentWillValue), $this->createRoller2d6Plus($currentRollValue)),
                "Even for will $currentWillValue and roll $currentRollValue has been expected previous malus $expectedMalus"
            );
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            self::assertSame($expectedMalus, $health->getSignificantMalus());
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $health->healSeriousWound($seriousWound, $this->createHealingPower(5, 3)); // "resetting" currently given wound
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            // low values to ensure untouched malus (should not be increased, therefore changed here at all, on heal)
            $health->rollAgainstMalusFromWounds($this->createWill(-1), $this->createRoller2d6Plus(-1));
        }
    }

    /**
     * @test
     */
    public function Malus_is_not_increased_on_new_heal_by_worse_roll()
    {
        $health = $this->createHealthToTest(5);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(0, $health->getSignificantMalus());

        // 3 ordinary wounds to reach some malus
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(2), SpecificWoundOrigin::getElementalWoundOrigin());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(2), SpecificWoundOrigin::getElementalWoundOrigin());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(2), SpecificWoundOrigin::getElementalWoundOrigin());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->rollAgainstMalusFromWounds($this->createWill(0), $this->createRoller2d6Plus(11));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(-1, $health->getSignificantMalus());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(1, $health->healNewOrdinaryWoundsUpTo($this->createHealingPower(1, 1)));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->rollAgainstMalusFromWounds($this->createWill(0), $this->createRoller2d6Plus(-2)); // much worse roll
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(-1, $health->getSignificantMalus(), 'Malus should not be increased');
    }

    // AFFLICTION

    /**
     * @test
     */
    public function I_can_add_affliction()
    {
        $health = $this->createHealthToTest(5);
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
        $health = $this->createHealthToTest(5);
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
        $health = $this->createHealthToTest(5);
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
        $health = $this->createHealthToTest(5);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $seriousWound = $health->createWound(
            $this->createWoundSize(6),
            SpecificWoundOrigin::getElementalWoundOrigin()
        );
        $anotherHealth = $this->createHealthToTest(5);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $anotherHealth->addAffliction($this->createAffliction($seriousWound));
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
        self::assertSame(
            5 /* power of 4 heals up to 5 wounds, see WoundsTable and related bonus-to-value conversion */,
            $health->healNewOrdinaryWoundsUpTo(new HealingPower(4, new WoundsTable()))
        );
        self::assertSame(20, $health->getRemainingHealthAmount());
        self::assertSame(1, $health->getUnhealedWoundsSum());
        self::assertSame(0, $health->getUnhealedNewOrdinaryWoundsSum(), 'All ordinary wounds should become "old" after heal');
        self::assertSame(0, $health->getUnhealedSeriousWoundsSum());
        self::assertSame(0, $health->getNumberOfSeriousInjuries());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(0, $health->getSignificantMalus());
        self::assertFalse($health->needsToRollAgainstMalus());
        self::assertNull($health->getReasonToRollAgainstMalus());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(
            0,
            $health->healNewOrdinaryWoundsUpTo($this->createHealingPower(10, 0)),
            'Nothing should be healed as a "new ordinary wound: because of treatment boundary'
        );
        self::assertSame(20, $health->getRemainingHealthAmount());
        self::assertSame(1, $health->getUnhealedWoundsSum());
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
        $collectedWounds = $this->sortObjects($collectedWounds);
        $unhealedWounds = $this->sortObjects($health->getUnhealedWounds()->toArray());
        self::assertSame($unhealedWounds, $collectedWounds);
        self::assertCount(2, $health->getUnhealedWounds());
        self::assertSame(8, $woundSum);
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

    private function sortObjects(array $objects)
    {
        usort($objects, function ($object1, $object2) {
            return strcasecmp(spl_object_hash($object1), spl_object_hash($object2));
        });

        return $objects;
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

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(-3, $health->rollAgainstMalusFromWounds($this->createWill(-1), $this->createRoller2d6Plus(1)));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
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
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(0, $health->getSignificantMalus(), 'Malus should be gone because of low damage after heal');

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(3, $health->healSeriousWound($seriousWoundByStab, $this->createHealingPower(10, 3)));
        self::assertSame(16, $health->getRemainingHealthAmount());
        self::assertCount(1, $health->getUnhealedWounds());
        self::assertSame(2, $health->getUnhealedWoundsSum());
        self::assertSame(0, $health->getUnhealedNewOrdinaryWoundsSum());
        self::assertSame(2, $health->getUnhealedSeriousWoundsSum());
        self::assertSame(1, $health->getNumberOfSeriousInjuries(), 'Single serious wound is unhealed');
    }

    /**
     * @test
     * @expectedException \DrdPlus\Person\Health\Exceptions\UnknownSeriousWoundToHeal
     */
    public function I_can_not_heal_serious_wound_from_different_health()
    {
        $health = $this->createHealthToTest(5);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $seriousWound = $health->createWound($this->createWoundSize(5), SpecificWoundOrigin::getMechanicalCutWoundOrigin());
        $anotherHealth = $this->createHealthToTest(3);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $anotherHealth->healSeriousWound($seriousWound, $this->createHealingPower());
    }

    /**
     * @test
     * @expectedException \DrdPlus\Person\Health\Exceptions\UnknownSeriousWoundToHeal
     */
    public function I_can_not_heal_serious_wound_not_created_by_current_health()
    {
        $health = $this->createHealthToTest(5);
        $healthReflection = new \ReflectionClass($health);
        $openForNewWound = $healthReflection->getProperty('openForNewWound');
        $openForNewWound->setAccessible(true);
        $openForNewWound->setValue($health, true);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $seriousWound = new SeriousWound($health, $this->createWoundSize(5), SpecificWoundOrigin::getMechanicalCutWoundOrigin());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->healSeriousWound($seriousWound, $this->createHealingPower());
    }

    /**
     * @test
     * @expectedException \DrdPlus\Person\Health\Exceptions\ExpectedFreshWoundToHeal
     */
    public function I_can_not_heal_old_serious_wound()
    {
        $health = $this->createHealthToTest(5);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $seriousWound = $health->createWound(
            $this->createWoundSize(5),
            SpecificWoundOrigin::getMechanicalCutWoundOrigin()
        );
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->rollAgainstMalusFromWounds($this->createWill(0), $this->createRoller2d6Plus(10));
        self::assertTrue($seriousWound->isSerious());
        $seriousWound->setOld();
        self::assertTrue($seriousWound->isOld());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->healSeriousWound($seriousWound, $this->createHealingPower());
    }

    /**
     * @test
     * @expectedException \DrdPlus\Person\Health\Exceptions\ExpectedFreshWoundToHeal
     */
    public function I_can_not_heal_already_treated_serious_wound()
    {
        $health = $this->createHealthToTest(5);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $seriousWound = $health->createWound(
            $this->createWoundSize(5),
            SpecificWoundOrigin::getMechanicalCutWoundOrigin()
        );
        self::assertTrue($seriousWound->isSerious());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->rollAgainstMalusFromWounds($this->createWill(123), $this->createRoller2d6Plus(321));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        try {
            $health->healSeriousWound($seriousWound, $this->createHealingPower(3, 3));
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getMessage() . "\n" . $exception->getTraceAsString());
        }
        self::assertTrue($seriousWound->isOld());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->healSeriousWound($seriousWound, $this->createHealingPower());
    }

    /**
     * @test
     */
    public function I_can_be_wounded_both_ordinary_and_seriously()
    {
        $health = $this->createHealthToTest(4);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(1), SpecificWoundOrigin::getMechanicalCrushWoundOrigin());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(1), SpecificWoundOrigin::getMechanicalCrushWoundOrigin());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(5), SpecificWoundOrigin::getMechanicalCrushWoundOrigin());
        self::assertSame(2, $health->getUnhealedNewOrdinaryWoundsSum());
        self::assertSame(5, $health->getUnhealedSeriousWoundsSum());
        self::assertSame(
            $health->getUnhealedNewOrdinaryWoundsSum(),
            $health->getUnhealedWoundsSum() - $health->getTreatmentBoundary()->getValue()
        );
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->rollAgainstMalusFromWounds($this->createWill(1), $this->createRoller2d6Plus(5));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(0, $health->healNewOrdinaryWoundsUpTo($this->createHealingPower(-21, 0)));
        self::assertSame(0, $health->getUnhealedNewOrdinaryWoundsSum(), 'All ordinary wounds should be marked as old');
        self::assertSame(
            $health->getUnhealedNewOrdinaryWoundsSum(),
            $health->getUnhealedWoundsSum() - $health->getTreatmentBoundary()->getValue()
        );
    }

    /**
     * @test
     */
    public function Nothing_changes_if_trying_to_change_wound_boundary_to_very_same()
    {
        $health = $this->createHealthToTest(123);
        $health->changeWoundBoundary($woundBoundary = $this->createWoundBoundary(123));
        self::assertSame(123, $health->getWoundBoundaryValue());
        $this->assertUnwounded($health, $woundBoundary);
    }

    /**
     * @test
     */
    public function I_get_highest_malus_from_wound_and_pains()
    {
        $health = $this->createHealthToTest(12);
        $damnSeriousWound = $health->createWound($this->createWoundSize(15), SpecificWoundOrigin::getPsychicalWoundOrigin());
        $health->rollAgainstMalusFromWounds($this->createWill(1), $this->createRoller2d6Plus(7));
        self::assertSame(-2, $health->getSignificantMalus());
        $health->addAffliction($this->createAffliction($damnSeriousWound));
        self::assertSame(-2, $health->getSignificantMalus());
        $health->addAffliction($this->createPain($damnSeriousWound, -5));
        self::assertSame(-5, $health->getSignificantMalus());
    }

    /**
     * @param $malus
     * @param Wound $wound
     * @return \Mockery\MockInterface|Pain
     */
    private function createPain(Wound $wound, $malus)
    {
        $pain = $this->mockery(Pain::class);
        $pain->shouldReceive('getSeriousWound')
            ->andReturn($wound);
        $pain->shouldReceive('getMalus')
            ->andReturn($malus);

        return $pain;
    }

    /**
     * @test
     */
    public function I_can_get_all_pains_and_afflictions()
    {
        $health = $this->createHealthToTest(123);
        $seriousWound = $health->createWound($this->createWoundSize(70), SpecificWoundOrigin::getPsychicalWoundOrigin());
        $health->addAffliction($firstPain = $this->createPain($seriousWound, -10));
        $health->addAffliction($someAffliction = $this->createAffliction($seriousWound));
        $health->addAffliction($secondPain = $this->createPain($seriousWound, -20));
        $health->addAffliction($thirdPain = $this->createPain($seriousWound, -30));
        self::assertSame($this->sortObjects([$firstPain, $secondPain, $thirdPain]), $this->sortObjects($health->getPains()->toArray()));
        self::assertSame($this->sortObjects([$firstPain, $secondPain, $someAffliction, $thirdPain]), $this->sortObjects($health->getAfflictions()->toArray()));
    }
}