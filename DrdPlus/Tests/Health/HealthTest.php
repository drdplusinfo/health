<?php
namespace DrdPlus\Tests\Health;

use DrdPlus\Codes\Body\OrdinaryWoundOriginCode;
use DrdPlus\Codes\Body\SeriousWoundOriginCode;
use DrdPlus\DiceRolls\Templates\Rollers\Roller2d6DrdPlus;
use DrdPlus\DiceRolls\Templates\Rolls\Roll2d6DrdPlus;
use DrdPlus\Health\Afflictions\AfflictionByWound;
use DrdPlus\Health\Afflictions\AfflictionName;
use DrdPlus\Health\Afflictions\SpecificAfflictions\Pain;
use DrdPlus\Health\GridOfWounds;
use DrdPlus\Health\HealingPower;
use DrdPlus\Health\Health;
use DrdPlus\Health\Inflictions\Glared;
use DrdPlus\Health\ReasonToRollAgainstWoundMalus;
use DrdPlus\Health\SeriousWound;
use DrdPlus\Health\TreatmentBoundary;
use DrdPlus\Health\Wound;
use DrdPlus\Health\WoundSize;
use DrdPlus\Lighting\Glare;
use DrdPlus\Properties\Base\Will;
use DrdPlus\Properties\Derived\Toughness;
use DrdPlus\Properties\Derived\WoundBoundary;
use DrdPlus\Tables\Measurements\Wounds\Wounds;
use DrdPlus\Tables\Measurements\Wounds\WoundsBonus;
use DrdPlus\Tables\Measurements\Wounds\WoundsTable;
use DrdPlus\Tables\Tables;
use Granam\Tests\Tools\TestWithMockery;

/** @noinspection LongInheritanceChainInspection */
class HealthTest extends TestWithMockery
{
    /**
     * @test
     */
    public function I_can_use_it()
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(123));
        self::assertSame(369, $health->getRemainingHealthAmount($woundBoundary));
        self::assertSame(369, $health->getHealthMaximum($woundBoundary));
    }

    /**
     * @param WoundBoundary $woundBoundary
     * @return Health
     */
    private function createHealthToTest(WoundBoundary $woundBoundary): Health
    {
        $health = new Health();
        $this->assertUnwounded($health, $woundBoundary);

        return $health;
    }

    /**
     * @param $value
     * @return \Mockery\MockInterface|WoundBoundary
     */
    private function createWoundBoundary($value)
    {
        $woundBoundary = $this->mockery(WoundBoundary::class);
        $woundBoundary->shouldReceive('getValue')
            ->andReturn($value);

        return $woundBoundary;
    }

    private function assertUnwounded(Health $health, WoundBoundary $woundBoundary)
    {
        self::assertNull($health->getId(), 'Not yet persisted health should not has filled ID (it is database responsibility in this case)');
        self::assertSame($health->getGridOfWounds()->getWoundsPerRowMaximum($woundBoundary), $woundBoundary->getValue());
        self::assertSame($health->getGridOfWounds()->getWoundsPerRowMaximum($woundBoundary) * 3, $health->getHealthMaximum($woundBoundary));
        self::assertSame($health->getGridOfWounds()->getWoundsPerRowMaximum($woundBoundary) * 3, $health->getRemainingHealthAmount($woundBoundary));
        self::assertCount(0, $health->getUnhealedWounds());
        self::assertSame(0, $health->getUnhealedNewOrdinaryWoundsSum());
        self::assertSame(0, $health->getUnhealedSeriousWoundsSum());
        self::assertSame(0, $health->getNumberOfSeriousInjuries());
        self::assertCount(0, $health->getAfflictions());
        self::assertSame(0, $health->getSignificantMalusFromPains($woundBoundary));
        self::assertCount(0, $health->getPains());
        self::assertTrue($health->isAlive($woundBoundary));
        self::assertTrue($health->isConscious($woundBoundary));
        self::assertFalse($health->needsToRollAgainstMalus());
        self::assertNull($health->getReasonToRollAgainstWoundMalus());

        self::assertInstanceOf(TreatmentBoundary::class, $health->getTreatmentBoundary());
        self::assertSame(0, $health->getTreatmentBoundary()->getValue());

        self::assertInstanceOf(GridOfWounds::class, $health->getGridOfWounds());
    }

    /**
     * @test
     * @dataProvider provideConsciousAndAlive
     * @param int $woundBoundaryValue
     * @param int $wound
     * @param bool $isConscious
     * @param bool $isAlive
     */
    public function I_can_easily_find_out_if_creature_is_conscious_and_alive($woundBoundaryValue, $wound, $isConscious, $isAlive)
    {
        $woundBoundary = $this->createWoundBoundary($woundBoundaryValue);
        $health = $this->createHealthToTest($woundBoundary);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound(
            $this->createWoundSize($wound),
            SeriousWoundOriginCode::getElementalWoundOrigin(),
            $woundBoundary
        );

        self::assertSame($isConscious, $health->isConscious($woundBoundary));
        self::assertSame($isAlive, $health->isAlive($woundBoundary));
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
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(10));
        self::assertSame(0, $health->getTreatmentBoundary()->getValue());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(4), SeriousWoundOriginCode::getMechanicalCutWoundOrigin(), $woundBoundary);
        self::assertSame(0, $health->getTreatmentBoundary()->getValue());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->healFreshOrdinaryWounds(
            $this->createHealingPower(1, 1),
            $this->createToughness(123),
            $this->createTablesWithWoundsTable($woundBoundary->getValue())
        );
        self::assertSame(3, $health->getTreatmentBoundary()->getValue());
        self::assertSame($health->getUnhealedWoundsSum(), $health->getTreatmentBoundary()->getValue());
    }

    /**
     * @param $value
     * @return \Mockery\MockInterface|Toughness
     */
    private function createToughness($value)
    {
        $toughness = $this->mockery(Toughness::class);
        $toughness->shouldReceive('getValue')
            ->andReturn($value);

        return $toughness;
    }

    /**
     * @param $woundsValue
     * @return \Mockery\MockInterface|Tables
     */
    private function createTablesWithWoundsTable($woundsValue)
    {
        $tables = $this->mockery(Tables::class);
        $tables->shouldReceive('getWoundsTable')
            ->andReturn($woundsTable = $this->mockery(WoundsTable::class));
        $woundsTable->shouldReceive('toWounds')
            ->andReturn($wounds = $this->mockery(Wounds::class));
        $wounds->shouldReceive('getValue')
            ->andReturn($woundsValue);
        $woundsTable->shouldReceive('toBonus')
            ->andReturn($woundsBonus = $this->mockery(WoundsBonus::class));
        /** just for @see \DrdPlus\Properties\Partials\WithHistoryTrait::extractArgumentsDescription */
        $woundsBonus->shouldReceive('getValue')
            ->andReturn(789);

        return $tables;
    }

    /**
     * @test
     */
    public function I_get_treatment_boundary_increased_by_serious_wound_immediately()
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(10));
        self::assertSame(0, $health->getTreatmentBoundary()->getValue());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(7), SeriousWoundOriginCode::getMechanicalCutWoundOrigin(), $woundBoundary);
        self::assertSame(7, $health->getTreatmentBoundary()->getValue());
        self::assertSame($health->getUnhealedWoundsSum(), $health->getTreatmentBoundary()->getValue());
    }

    /**
     * @test
     */
    public function I_get_treatment_boundary_lowered_by_healed_serious_wound()
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(10));
        self::assertSame(0, $health->getTreatmentBoundary()->getValue());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $seriousWound = $health->createWound($this->createWoundSize(7), SeriousWoundOriginCode::getMechanicalCutWoundOrigin(), $woundBoundary);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->healFreshSeriousWound(
            $seriousWound,
            $this->createHealingPower(5),
            $this->createToughness(123),
            $this->createTablesWithWoundsTable($woundBoundary->getValue())
        );
        self::assertSame(2, $health->getTreatmentBoundary()->getValue());
    }

    /**
     * @test
     */
    public function I_do_not_have_lowered_treatment_boundary_by_healed_ordinary_wound()
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(10));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(3), SeriousWoundOriginCode::getMechanicalCrushWoundOrigin(), $woundBoundary);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(6), SeriousWoundOriginCode::getMechanicalCutWoundOrigin(), $woundBoundary);
        self::assertSame(6, $health->getTreatmentBoundary()->getValue());
        self::assertSame(9, $health->getUnhealedWoundsSum());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->healFreshOrdinaryWounds(
            $this->createHealingPower(999, 3),
            $this->createToughness(123),
            $this->createTablesWithWoundsTable($woundBoundary->getValue())
        );
        self::assertSame(6, $health->getTreatmentBoundary()->getValue());
        self::assertSame(6, $health->getUnhealedWoundsSum());
    }

    /**
     * @test
     */
    public function I_get_treatment_boundary_lowered_by_regenerated_amount()
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(10));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(3), SeriousWoundOriginCode::getMechanicalCrushWoundOrigin(), $woundBoundary);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(6), SeriousWoundOriginCode::getMechanicalCutWoundOrigin(), $woundBoundary);
        self::assertSame(6, $health->getTreatmentBoundary()->getValue());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->regenerate(
            HealingPower::createForTreatment(9, Tables::getIt()),
            $this->createToughness(-1),
            $this->createTablesWithWoundsTable($woundBoundary->getValue())
        );
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
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(10));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(10), SeriousWoundOriginCode::getElementalWoundOrigin(), $woundBoundary);
        self::assertTrue($health->needsToRollAgainstMalus());
        self::assertSame(ReasonToRollAgainstWoundMalus::getWoundReason(), $health->getReasonToRollAgainstWoundMalus());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(
            $expectedMalus,
            $health->rollAgainstMalusFromWounds(
                $this->createWill($willValue),
                $this->createRoller2d6Plus($rollValue),
                $woundBoundary
            )
        );
        self::assertFalse($health->needsToRollAgainstMalus());
        self::assertNull($health->getReasonToRollAgainstWoundMalus());
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
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(10));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(4),
            SeriousWoundOriginCode::getElementalWoundOrigin(),
            $woundBoundary
        );
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(4),
            SeriousWoundOriginCode::getElementalWoundOrigin(),
            $woundBoundary
        );
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(4),
            SeriousWoundOriginCode::getElementalWoundOrigin(),
            $woundBoundary
        );
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->rollAgainstMalusFromWounds($this->createWill(-1),
            $this->createRoller2d6Plus(3),
            $woundBoundary
        ); // -3 malus as a result
        self::assertFalse($health->needsToRollAgainstMalus());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->healFreshOrdinaryWounds(
            $this->createHealingPower(1, 1),
            $this->createToughness(123),
            $this->createTablesWithWoundsTable($woundBoundary->getValue())
        );
        self::assertSame(ReasonToRollAgainstWoundMalus::getHealReason(), $health->getReasonToRollAgainstWoundMalus());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(
            $expectedMalus,
            $health->rollAgainstMalusFromWounds(
                $this->createWill($willValue),
                $this->createRoller2d6Plus($rollValue),
                $woundBoundary
            )
        );
        self::assertFalse($health->needsToRollAgainstMalus());
        self::assertNull($health->getReasonToRollAgainstWoundMalus());
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
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(10));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $seriousWound = $health->createWound($this->createWoundSize(15),
            SeriousWoundOriginCode::getElementalWoundOrigin(),
            $woundBoundary
        );
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->rollAgainstMalusFromWounds($this->createWill(-1),
            $this->createRoller2d6Plus(3),
            $woundBoundary
        ); // -3 malus as a result
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->healFreshSeriousWound($seriousWound,
            $this->createHealingPower(1),
            $this->createToughness(123),
            $this->createTablesWithWoundsTable($woundBoundary->getValue())
        );
        self::assertSame(ReasonToRollAgainstWoundMalus::getHealReason(), $health->getReasonToRollAgainstWoundMalus());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(
            $expectedMalus,
            $health->rollAgainstMalusFromWounds(
                $this->createWill($willValue),
                $this->createRoller2d6Plus($rollValue),
                $woundBoundary
            )
        );
        self::assertFalse($health->needsToRollAgainstMalus());
        self::assertNull($health->getReasonToRollAgainstWoundMalus());
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
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(10));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(15),
            SeriousWoundOriginCode::getElementalWoundOrigin(),
            $woundBoundary
        );
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->rollAgainstMalusFromWounds($this->createWill(-1),
            $this->createRoller2d6Plus(3),
            $woundBoundary
        ); // -3 malus as a result
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->regenerate(
            $this->createHealingPower(5, 5),
            $this->createToughness(123),
            $this->createTablesWithWoundsTable($woundBoundary->getValue())
        );
        self::assertSame(ReasonToRollAgainstWoundMalus::getHealReason(), $health->getReasonToRollAgainstWoundMalus());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(
            $expectedMalus,
            $health->rollAgainstMalusFromWounds(
                $this->createWill($willValue),
                $this->createRoller2d6Plus($rollValue),
                $woundBoundary
            )
        );
        self::assertFalse($health->needsToRollAgainstMalus());
        self::assertNull($health->getReasonToRollAgainstWoundMalus());
    }

    /**
     * @test
     * @expectedException \DrdPlus\Health\Exceptions\UselessRollAgainstMalus
     */
    public function I_can_not_roll_on_malus_from_wounds_if_not_needed()
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(10));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->rollAgainstMalusFromWounds($this->createWill(),
            $this->createRoller2d6Plus(),
            $woundBoundary
        );
    }

    // ROLL ON MALUS EXPECTED

    /**
     * @test
     * @expectedException \DrdPlus\Health\Exceptions\NeedsToRollAgainstMalusFirst
     */
    public function I_can_not_add_new_wound_if_roll_on_malus_expected()
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(10));
        try {
            $health->createWound($this->createWoundSize(10),
                SeriousWoundOriginCode::getElementalWoundOrigin(),
                $woundBoundary
            );
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getTraceAsString());
        }
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(10),
            SeriousWoundOriginCode::getElementalWoundOrigin(),
            $woundBoundary
        );
    }

    /**
     * @test
     * @expectedException \DrdPlus\Health\Exceptions\NeedsToRollAgainstMalusFirst
     */
    public function I_can_not_heal_fresh_ordinary_wounds_if_roll_on_malus_expected()
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(10));
        try {
            $health->createWound(
                $this->createWoundSize(4),
                SeriousWoundOriginCode::getElementalWoundOrigin(),
                $woundBoundary
            );
            $health->createWound(
                $this->createWoundSize(4),
                SeriousWoundOriginCode::getElementalWoundOrigin(),
                $woundBoundary
            );
            $health->createWound(
                $this->createWoundSize(4),
                SeriousWoundOriginCode::getElementalWoundOrigin(),
                $woundBoundary
            );
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getTraceAsString());
        }
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->healFreshOrdinaryWounds(
            $this->createHealingPower(5),
            $this->createToughness(123),
            $this->createTablesWithWoundsTable($woundBoundary->getValue())
        );
    }

    /**
     * @test
     * @expectedException \DrdPlus\Health\Exceptions\NeedsToRollAgainstMalusFirst
     */
    public function I_can_not_heal_serious_wound_if_roll_on_malus_expected()
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(10));
        try {
            $seriousWound = $health->createWound($this->createWoundSize(14),
                SeriousWoundOriginCode::getElementalWoundOrigin(),
                $woundBoundary
            );
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getTraceAsString());
        }
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        $health->healFreshSeriousWound($seriousWound,
            $this->createHealingPower(5),
            $this->createToughness(123),
            $this->createTablesWithWoundsTable($woundBoundary->getValue())
        );
    }

    /**
     * @test
     * @expectedException \DrdPlus\Health\Exceptions\NeedsToRollAgainstMalusFirst
     */
    public function I_can_not_regenerate_if_roll_on_malus_expected()
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(10));
        try {
            $health->createWound($this->createWoundSize(14),
                SeriousWoundOriginCode::getElementalWoundOrigin(),
                $woundBoundary
            );
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getTraceAsString());
        }
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->regenerate(
            $this->createHealingPower(5),
            $this->createToughness(123),
            $this->createTablesWithWoundsTable($woundBoundary->getValue())
        );
    }

    /**
     * @test
     * @expectedException \DrdPlus\Health\Exceptions\NeedsToRollAgainstMalusFirst
     */
    public function I_can_not_get_malus_from_wounds_if_roll_on_it_expected()
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(10));
        try {
            $health->createWound(
                $this->createWoundSize(14),
                SeriousWoundOriginCode::getElementalWoundOrigin(),
                $woundBoundary
            );
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getTraceAsString());
        }
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->getSignificantMalusFromPains($woundBoundary);
    }

    // MALUS CONDITIONAL CHANGES

    /**
     * @test
     * @dataProvider provideRollForMalus
     * @param $willValue
     * @param $rollValue
     * @param $expectedMalus
     */
    public function Malus_can_increase_on_fresh_wound($willValue, $rollValue, $expectedMalus)
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(5));

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound(
            $this->createWoundSize(5),
            SeriousWoundOriginCode::getElementalWoundOrigin(),
            $woundBoundary
        );
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame($expectedMalus, $health->rollAgainstMalusFromWounds($this->createWill($willValue),
            $this->createRoller2d6Plus($rollValue),
            $woundBoundary
        ));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame($expectedMalus, $health->getSignificantMalusFromPains($woundBoundary));

        for ($currentWillValue = $willValue, $currentRollValue = $rollValue;
             $currentRollValue > -2 && $currentWillValue > -2;
             $currentRollValue--, $currentWillValue--
        ) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $seriousWound = $health->createWound($this->createWoundSize(3),
                SeriousWoundOriginCode::getElementalWoundOrigin(),
                $woundBoundary
            );
            $currentlyExpectedMalus = max(0, min(3, (int)floor(($currentWillValue + $currentRollValue) / 5))) - 3; // 0; -1; -2; -3
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            self::assertSame(
                $currentlyExpectedMalus, // malus can increase (be more negative)
                $health->rollAgainstMalusFromWounds(
                    $this->createWill($currentWillValue),
                    $this->createRoller2d6Plus($currentRollValue),
                    $woundBoundary
                ),
                "For will $currentWillValue and roll $currentRollValue has been expected malus $currentlyExpectedMalus"
            );
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            self::assertSame($currentlyExpectedMalus, $health->getSignificantMalusFromPains($woundBoundary));
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $health->healFreshSeriousWound($seriousWound,
                $this->createHealingPower(5, 3),
                $this->createToughness(123),
                $this->createTablesWithWoundsTable($woundBoundary->getValue())
            ); // "resetting" currently given wound
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            // low values to ensure untouched malus (should not be increased, therefore changed here at all, on heal)
            $health->rollAgainstMalusFromWounds($this->createWill(-1),
                $this->createRoller2d6Plus(-1),
                $woundBoundary
            );
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
    public function Malus_can_not_decrease_on_fresh_wound($willValue, $rollValue, $expectedMalus)
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(5));

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(5),
            SeriousWoundOriginCode::getElementalWoundOrigin(),
            $woundBoundary
        );
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame($expectedMalus, $health->rollAgainstMalusFromWounds($this->createWill($willValue),
            $this->createRoller2d6Plus($rollValue),
            $woundBoundary
        ));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame($expectedMalus, $health->getSignificantMalusFromPains($woundBoundary));

        for ($currentWillValue = $willValue, $currentRollValue = $rollValue;
             $currentRollValue < 16 && $currentWillValue < 10;
             $currentRollValue++, $currentWillValue++
        ) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $seriousWound = $health->createWound($this->createWoundSize(3),
                SeriousWoundOriginCode::getElementalWoundOrigin(),
                $woundBoundary
            );
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            self::assertSame(
                $expectedMalus, // malus should not be decreased (be closer to zero)
                $health->rollAgainstMalusFromWounds(
                    $this->createWill($currentWillValue),
                    $this->createRoller2d6Plus($currentRollValue),
                    $woundBoundary
                ),
                "Even for will $currentWillValue and roll $currentRollValue has been expected previous malus $expectedMalus"
            );
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            self::assertSame($expectedMalus, $health->getSignificantMalusFromPains($woundBoundary));
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $health->healFreshSeriousWound($seriousWound,
                $this->createHealingPower(5, 3),
                $this->createToughness(123),
                $this->createTablesWithWoundsTable($woundBoundary->getValue())
            ); // "resetting" currently given wound
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            // low values to ensure untouched malus (should not be increased, therefore changed here at all, on heal)
            $health->rollAgainstMalusFromWounds($this->createWill(-1),
                $this->createRoller2d6Plus(-1),
                $woundBoundary
            );
        }
    }

    /**
     * @test
     */
    public function Malus_is_not_increased_on_new_heal_by_worse_roll()
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(5));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(0, $health->getSignificantMalusFromPains($woundBoundary));

        // 3 ordinary wounds to reach some malus
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(2),
            SeriousWoundOriginCode::getElementalWoundOrigin(),
            $woundBoundary
        );
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(2),
            SeriousWoundOriginCode::getElementalWoundOrigin(),
            $woundBoundary
        );
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(2),
            SeriousWoundOriginCode::getElementalWoundOrigin(),
            $woundBoundary
        );
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->rollAgainstMalusFromWounds($this->createWill(0),
            $this->createRoller2d6Plus(11),
            $woundBoundary
        );
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(-1, $health->getSignificantMalusFromPains($woundBoundary));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(
            1,
            $health->healFreshOrdinaryWounds(
                $this->createHealingPower(1, 1),
                $this->createToughness(123),
                $this->createTablesWithWoundsTable($woundBoundary->getValue())
            )
        );
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->rollAgainstMalusFromWounds($this->createWill(0),
            $this->createRoller2d6Plus(-2),
            $woundBoundary
        ); // much worse roll
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(-1, $health->getSignificantMalusFromPains($woundBoundary), 'Malus should not be increased');
    }

    // AFFLICTION

    /**
     * @test
     */
    public function I_can_add_affliction(): void
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(5));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $wound = $health->createWound($this->createWoundSize(5),
            SeriousWoundOriginCode::getMechanicalCrushWoundOrigin(),
            $woundBoundary
        );
        $affliction = $this->createAffliction($wound);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->addAffliction($affliction);
        self::assertCount(1, $health->getAfflictions());
        self::assertSame($affliction, $health->getAfflictions()->current());
    }

    /**
     * @param SeriousWound $seriousWound
     * @param array $values
     * @return \Mockery\MockInterface|AfflictionByWound
     */
    private function createAffliction(SeriousWound $seriousWound, array $values = [])
    {
        $afflictionByWound = $this->mockery(AfflictionByWound::class);
        $afflictionByWound->shouldReceive('getSeriousWound')
            ->andReturn($seriousWound);
        $afflictionByWound->shouldReceive('getName')
            ->andReturn($this->mockery(AfflictionName::class));
        foreach ($values as $valueName => $value) {
            $afflictionByWound->shouldReceive('get' . ucfirst($valueName))
                ->andReturn($value);
        }

        return $afflictionByWound;
    }

    /**
     * @test
     * @expectedException \DrdPlus\Health\Exceptions\UnknownAfflictionOriginatingWound
     */
    public function I_can_not_add_affliction_of_unknown_wound(): void
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(5));
        $affliction = $this->createAffliction($this->createSeriousWound());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->addAffliction($affliction);
    }

    /**
     * @return \Mockery\MockInterface|SeriousWound
     */
    private function createSeriousWound()
    {
        $wound = $this->mockery(SeriousWound::class);
        $wound->shouldReceive('getHealth')
            ->andReturn($this->mockery(Health::class));
        $wound->shouldReceive('getWoundOriginCode')
            ->andReturn(SeriousWoundOriginCode::getMechanicalCrushWoundOrigin());
        $wound->shouldReceive('__toString')
            ->andReturn('123');

        return $wound;
    }

    /**
     * @test
     * @expectedException \DrdPlus\Health\Exceptions\AfflictionIsAlreadyRegistered
     */
    public function I_can_not_add_same_affliction_twice()
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(5));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $wound = $health->createWound(
            $this->createWoundSize(6),

            SeriousWoundOriginCode::getElementalWoundOrigin()
            ,
            $woundBoundary
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
     * @expectedException \DrdPlus\Health\Exceptions\UnknownAfflictionOriginatingWound
     */
    public function I_can_not_add_affliction_with_to_health_unknown_wound()
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(5));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $seriousWound = $health->createWound(
            $this->createWoundSize(6),

            SeriousWoundOriginCode::getElementalWoundOrigin()
            ,
            $woundBoundary
        );
        $anotherHealth = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(5));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $anotherHealth->addAffliction($this->createAffliction($seriousWound));
    }

    // NEW WOUND

    /**
     * @test
     */
    public function I_can_be_ordinary_wounded()
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(5));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $ordinaryWound = $health->createWound(
            $this->createWoundSize(2),

            SeriousWoundOriginCode::getElementalWoundOrigin()
            ,
            $woundBoundary
        );
        self::assertInstanceOf(Wound::class, $ordinaryWound);
        self::assertSame(2, $ordinaryWound->getValue());
        self::assertSame(
            OrdinaryWoundOriginCode::getIt(),
            $ordinaryWound->getWoundOriginCode(),
            'The ordinary wound origin should be used on such small wound'
        );
        self::assertCount(1, $health->getUnhealedWounds());
        self::assertSame($ordinaryWound, $health->getUnhealedWounds()->last());
        self::assertSame(13, $health->getRemainingHealthAmount($woundBoundary));
        self::assertSame(2, $health->getUnhealedNewOrdinaryWoundsSum());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(0, $health->getSignificantMalusFromPains($woundBoundary));
        self::assertFalse($health->needsToRollAgainstMalus());
        self::assertNull($health->getReasonToRollAgainstWoundMalus());

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $anotherOrdinaryWound = $health->createWound(
            $this->createWoundSize(1),

            SeriousWoundOriginCode::getElementalWoundOrigin()
            ,
            $woundBoundary
        );
        self::assertInstanceOf(Wound::class, $anotherOrdinaryWound);
        self::assertSame(1, $anotherOrdinaryWound->getValue());
        self::assertSame(
            OrdinaryWoundOriginCode::getIt(),
            $anotherOrdinaryWound->getWoundOriginCode(),
            'The ordinary wound origin should be used on such small wound'
        );
        self::assertCount(2, $health->getUnhealedWounds());
        self::assertSame($anotherOrdinaryWound, $health->getUnhealedWounds()->last());
        self::assertSame([$ordinaryWound, $anotherOrdinaryWound], $health->getUnhealedWounds()->toArray());
        self::assertSame(3, $health->getUnhealedNewOrdinaryWoundsSum());
        self::assertSame(12, $health->getRemainingHealthAmount($woundBoundary));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(0, $health->getSignificantMalusFromPains($woundBoundary));
        self::assertFalse($health->needsToRollAgainstMalus());
        self::assertNull($health->getReasonToRollAgainstWoundMalus());
    }

    /**
     * @test
     */
    public function I_can_be_ordinary_healed()
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(7));

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(1),
            SeriousWoundOriginCode::getElementalWoundOrigin(),
            $woundBoundary
        );
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(3),
            SeriousWoundOriginCode::getMechanicalCrushWoundOrigin(),
            $woundBoundary
        );
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(2),
            SeriousWoundOriginCode::getMechanicalStabWoundOrigin(),
            $woundBoundary
        );

        self::assertSame(15, $health->getRemainingHealthAmount($woundBoundary));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(
            2 /* power of 4 - 3 (toughness) heals up to 2 wounds, see WoundsTable and related bonus-to-value conversion */,
            $health->healFreshOrdinaryWounds(
                HealingPower::createForTreatment(4, Tables::getIt()),
                $this->createToughness(-3),
                $this->createTablesWithWoundsTable($woundBoundary->getValue())
            )
        );
        self::assertSame(17, $health->getRemainingHealthAmount($woundBoundary));
        self::assertSame(4, $health->getUnhealedWoundsSum());
        self::assertSame(0, $health->getUnhealedNewOrdinaryWoundsSum(), 'All ordinary wounds should become "old" after heal');
        self::assertSame(0, $health->getUnhealedSeriousWoundsSum());
        self::assertSame(0, $health->getNumberOfSeriousInjuries());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(0, $health->getSignificantMalusFromPains($woundBoundary));
        self::assertFalse($health->needsToRollAgainstMalus());
        self::assertNull($health->getReasonToRollAgainstWoundMalus());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(
            0,
            $health->healFreshOrdinaryWounds(
                $this->createHealingPower(10, 0),
                $this->createToughness(123),
                $this->createTablesWithWoundsTable($woundBoundary->getValue())
            ),
            'Nothing should be healed as a "new ordinary wound: because of treatment boundary'
        );
        self::assertSame(17, $health->getRemainingHealthAmount($woundBoundary));
        self::assertSame(4, $health->getUnhealedWoundsSum());
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
            $decreasedHealingPower = $this->mockery(HealingPower::class);
            $decreasedHealingPower->shouldReceive('getHealUpTo')
                ->andReturn(0);
            /** @noinspection PhpUnusedParameterInspection */
            $healingPower->shouldReceive('decreaseByHealedAmount')
                ->zeroOrMoreTimes()
                ->andReturnUsing(function ($givenHealedAmount)
                use ($decreasedHealingPower, $expectedHealedAmount) {
                    self::assertSame($givenHealedAmount, $expectedHealedAmount);

                    return $decreasedHealingPower;
                });
        }

        return $healingPower;
    }

    /**
     * @test
     */
    public function I_can_be_seriously_wounded(): void
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(6));

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $seriousWoundByStab = $health->createWound(
            $this->createWoundSize(3),

            $seriousWoundOrigin = SeriousWoundOriginCode::getMechanicalStabWoundOrigin()
            ,
            $woundBoundary
        );
        self::assertInstanceOf(Wound::class, $seriousWoundByStab);
        self::assertSame(3, $seriousWoundByStab->getValue());
        self::assertSame($seriousWoundOrigin, $seriousWoundByStab->getWoundOriginCode());
        self::assertCount(1, $health->getUnhealedWounds());
        self::assertSame($seriousWoundByStab, $health->getUnhealedWounds()->last());
        self::assertSame(15, $health->getRemainingHealthAmount($woundBoundary));
        self::assertSame(0, $health->getUnhealedNewOrdinaryWoundsSum());
        self::assertSame(3, $health->getUnhealedSeriousWoundsSum());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(0, $health->getSignificantMalusFromPains($woundBoundary), 'There are not enough wounds to suffer from them yet.');
        self::assertFalse($health->needsToRollAgainstMalus());
        self::assertNull($health->getReasonToRollAgainstWoundMalus());

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $seriousWoundByPsyche = $health->createWound(
            $this->createWoundSize(5),

            $seriousWoundOrigin = SeriousWoundOriginCode::getPsychicalWoundOrigin()
            ,
            $woundBoundary
        );
        self::assertInstanceOf(Wound::class, $seriousWoundByPsyche);
        self::assertSame(5, $seriousWoundByPsyche->getValue());
        self::assertTrue($seriousWoundByPsyche->isSerious());
        self::assertSame($seriousWoundOrigin, $seriousWoundByPsyche->getWoundOriginCode());
        self::assertCount(2, $health->getUnhealedWounds());
        self::assertSame(0, $health->getUnhealedNewOrdinaryWoundsSum());
        self::assertSame(8, $health->getUnhealedSeriousWoundsSum());
        self::assertSame(8, $health->getUnhealedWoundsSum());
        self::assertSame(10, $health->getRemainingHealthAmount($woundBoundary));
        self::assertTrue($health->needsToRollAgainstMalus());
        self::assertSame(ReasonToRollAgainstWoundMalus::getWoundReason(), $health->getReasonToRollAgainstWoundMalus());
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
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(6));

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $seriousWoundByStab = $health->createWound(
            $this->createWoundSize(3),

            $seriousWoundOrigin = SeriousWoundOriginCode::getMechanicalStabWoundOrigin()
            ,
            $woundBoundary
        );
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $seriousWoundByPsyche = $health->createWound(
            $this->createWoundSize(5),

            $seriousWoundOrigin = SeriousWoundOriginCode::getPsychicalWoundOrigin()
            ,
            $woundBoundary
        );

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(-3, $health->rollAgainstMalusFromWounds($this->createWill(-1),
            $this->createRoller2d6Plus(1),
            $woundBoundary
        ));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(
            0,
            $health->healFreshOrdinaryWounds(
                $this->createHealingPower(1, 0),
                $this->createToughness(123),
                $this->createTablesWithWoundsTable($woundBoundary->getValue())
            ),
            'Nothing should be healed because there is no ordinary wound'
        );
        self::assertSame(8, $health->getUnhealedWoundsSum());
        self::assertCount(2, $health->getUnhealedWounds());
        self::assertSame(10, $health->getRemainingHealthAmount($woundBoundary));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(3, $health->healFreshSeriousWound($seriousWoundByPsyche,
            $this->createHealingPower(3, 3),
            $this->createToughness(123),
            $this->createTablesWithWoundsTable($woundBoundary->getValue())
        ));
        self::assertSame(13, $health->getRemainingHealthAmount($woundBoundary));
        self::assertCount(2, $health->getUnhealedWounds());
        self::assertSame(5, $health->getUnhealedWoundsSum());
        self::assertSame(0, $health->getUnhealedNewOrdinaryWoundsSum());
        self::assertSame(5, $health->getUnhealedSeriousWoundsSum());
        self::assertSame(2, $health->getNumberOfSeriousInjuries(), 'Both serious wounds are still unhealed');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(0, $health->getSignificantMalusFromPains($woundBoundary), 'Malus should be gone because of low damage after heal');

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(3, $health->healFreshSeriousWound($seriousWoundByStab,
            $this->createHealingPower(10, 3),
            $this->createToughness(123),
            $this->createTablesWithWoundsTable($woundBoundary->getValue())
        ));
        self::assertSame(16, $health->getRemainingHealthAmount($woundBoundary));
        self::assertCount(1, $health->getUnhealedWounds());
        self::assertSame(2, $health->getUnhealedWoundsSum());
        self::assertSame(0, $health->getUnhealedNewOrdinaryWoundsSum());
        self::assertSame(2, $health->getUnhealedSeriousWoundsSum());
        self::assertSame(1, $health->getNumberOfSeriousInjuries(), 'Single serious wound is unhealed');
    }

    /**
     * @test
     * @expectedException \DrdPlus\Health\Exceptions\UnknownSeriousWoundToHeal
     */
    public function I_can_not_heal_serious_wound_from_different_health()
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(5));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $seriousWound = $health->createWound($this->createWoundSize(5),
            SeriousWoundOriginCode::getMechanicalCutWoundOrigin(),
            $woundBoundary
        );
        $anotherHealth = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(3));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $anotherHealth->healFreshSeriousWound($seriousWound,
            $this->createHealingPower(),
            $this->createToughness(123),
            $this->createTablesWithWoundsTable($woundBoundary->getValue())
        );
    }

    /**
     * @test
     * @expectedException \DrdPlus\Health\Exceptions\UnknownSeriousWoundToHeal
     * @throws \ReflectionException
     */
    public function I_can_not_heal_serious_wound_not_created_by_current_health()
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(5));
        $healthReflection = new \ReflectionClass($health);
        $openForNewWound = $healthReflection->getProperty('openForNewWound');
        $openForNewWound->setAccessible(true);
        $openForNewWound->setValue($health, true);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $seriousWound = new SeriousWound($health, $this->createWoundSize(5), SeriousWoundOriginCode::getMechanicalCutWoundOrigin());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->healFreshSeriousWound($seriousWound,
            $this->createHealingPower(),
            $this->createToughness(123),
            $this->createTablesWithWoundsTable($woundBoundary->getValue())
        );
    }

    /**
     * @test
     * @expectedException \DrdPlus\Health\Exceptions\ExpectedFreshWoundToHeal
     */
    public function I_can_not_heal_old_serious_wound()
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(5));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $seriousWound = $health->createWound(
            $this->createWoundSize(5),

            SeriousWoundOriginCode::getMechanicalCutWoundOrigin()
            ,
            $woundBoundary
        );
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->rollAgainstMalusFromWounds($this->createWill(0),
            $this->createRoller2d6Plus(10),
            $woundBoundary
        );
        self::assertTrue($seriousWound->isSerious());
        $seriousWound->setOld();
        self::assertTrue($seriousWound->isOld());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->healFreshSeriousWound($seriousWound,
            $this->createHealingPower(),
            $this->createToughness(123),
            $this->createTablesWithWoundsTable($woundBoundary->getValue())
        );
    }

    /**
     * @test
     * @expectedException \DrdPlus\Health\Exceptions\ExpectedFreshWoundToHeal
     */
    public function I_can_not_heal_already_treated_serious_wound()
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(5));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $seriousWound = $health->createWound(
            $this->createWoundSize(5),

            SeriousWoundOriginCode::getMechanicalCutWoundOrigin()
            ,
            $woundBoundary
        );
        self::assertTrue($seriousWound->isSerious());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->rollAgainstMalusFromWounds($this->createWill(123),
            $this->createRoller2d6Plus(321),
            $woundBoundary
        );
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        try {
            $health->healFreshSeriousWound($seriousWound,
                $this->createHealingPower(3, 3),
                $this->createToughness(123),
                $this->createTablesWithWoundsTable($woundBoundary->getValue())
            );
        } catch (\Exception $exception) {
            self::fail('No exception expected so far: ' . $exception->getMessage() . "\n" . $exception->getTraceAsString());
        }
        self::assertTrue($seriousWound->isOld());
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->healFreshSeriousWound($seriousWound,
            $this->createHealingPower(),
            $this->createToughness(123),
            $this->createTablesWithWoundsTable($woundBoundary->getValue())
        );
    }

    /**
     * @test
     */
    public function I_can_be_wounded_both_ordinary_and_seriously()
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(4));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(1),
            SeriousWoundOriginCode::getMechanicalCrushWoundOrigin(),
            $woundBoundary
        );
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(1),
            SeriousWoundOriginCode::getMechanicalCrushWoundOrigin(),
            $woundBoundary
        );
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->createWound($this->createWoundSize(5),
            SeriousWoundOriginCode::getMechanicalCrushWoundOrigin(),
            $woundBoundary
        );
        self::assertSame(2, $health->getUnhealedNewOrdinaryWoundsSum());
        self::assertSame(5, $health->getUnhealedSeriousWoundsSum());
        self::assertSame(
            $health->getUnhealedNewOrdinaryWoundsSum(),
            $health->getUnhealedWoundsSum() - $health->getTreatmentBoundary()->getValue()
        );
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $health->rollAgainstMalusFromWounds($this->createWill(1),
            $this->createRoller2d6Plus(5),
            $woundBoundary
        );
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::assertSame(
            0,
            $health->healFreshOrdinaryWounds($this->createHealingPower(-21, 0),
                $this->createToughness(123),
                $this->createTablesWithWoundsTable($woundBoundary->getValue())
            )
        );
        self::assertSame(0, $health->getUnhealedNewOrdinaryWoundsSum(), 'All ordinary wounds should be marked as old');
        self::assertSame(
            $health->getUnhealedNewOrdinaryWoundsSum(),
            $health->getUnhealedWoundsSum() - $health->getTreatmentBoundary()->getValue()
        );
    }

    /**
     * @test
     */
    public function I_get_highest_malus_from_wound_and_pains()
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(12));
        $damnSeriousWound = $health->createWound($this->createWoundSize(15),
            SeriousWoundOriginCode::getPsychicalWoundOrigin(),
            $woundBoundary
        );
        $health->rollAgainstMalusFromWounds($this->createWill(1),
            $this->createRoller2d6Plus(7),
            $woundBoundary
        );
        self::assertSame(-2, $health->getSignificantMalusFromPains($woundBoundary));
        $health->addAffliction($this->createAffliction($damnSeriousWound));
        self::assertSame(-2, $health->getSignificantMalusFromPains($woundBoundary));
        $health->addAffliction($this->createPain($damnSeriousWound, ['malusToActivities' => -5]));
        self::assertSame(-5, $health->getSignificantMalusFromPains($woundBoundary));
    }

    /**
     * @param SeriousWound $seriousWound
     * @param array $maluses
     * @return \Mockery\MockInterface|Pain
     */
    private function createPain(SeriousWound $seriousWound, array $maluses = [])
    {
        $pain = $this->mockery(Pain::class);
        $pain->shouldReceive('getSeriousWound')
            ->andReturn($seriousWound);
        foreach ($maluses as $nameOfValue => $otherValue) {
            $pain->shouldReceive('get' . ucfirst($nameOfValue))
                ->andReturn($otherValue);
        }

        return $pain;
    }

    /**
     * @test
     */
    public function I_can_get_all_pains_and_afflictions()
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(123));
        $seriousWound = $health->createWound($this->createWoundSize(70),
            SeriousWoundOriginCode::getPsychicalWoundOrigin(),
            $woundBoundary
        );
        $health->addAffliction($firstPain = $this->createPain($seriousWound, ['malusToActivities' => -10]));
        $health->addAffliction($someAffliction = $this->createAffliction($seriousWound));
        $health->addAffliction($secondPain = $this->createPain($seriousWound, ['malusToActivities' => -20]));
        $health->addAffliction($thirdPain = $this->createPain($seriousWound, ['malusToActivities' => -30]));
        self::assertSame($this->sortObjects([$firstPain, $secondPain, $thirdPain]), $this->sortObjects($health->getPains()->toArray()));
        self::assertSame($this->sortObjects([$firstPain, $secondPain, $someAffliction, $thirdPain]), $this->sortObjects($health->getAfflictions()->toArray()));
    }

    /**
     * @test
     */
    public function I_can_get_strength_malus_from_afflictions()
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(123));
        $seriousWound = $health->createWound($this->createWoundSize(70),
            SeriousWoundOriginCode::getPsychicalWoundOrigin(),
            $woundBoundary
        );
        $health->addAffliction($this->createPain($seriousWound, ['strengthMalus' => -4]));
        $health->addAffliction($this->createAffliction($seriousWound, ['strengthMalus' => -1]));
        $health->addAffliction($this->createPain($seriousWound, ['strengthMalus' => 123]));

        self::assertSame(118, $health->getStrengthMalusFromAfflictions());
    }

    /**
     * @test
     */
    public function I_can_get_agility_malus_from_afflictions()
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(123));
        $seriousWound = $health->createWound($this->createWoundSize(70),
            SeriousWoundOriginCode::getPsychicalWoundOrigin(),
            $woundBoundary
        );
        $health->addAffliction($this->createPain($seriousWound, ['agilityMalus' => -1]));
        $health->addAffliction($this->createAffliction($seriousWound, ['agilityMalus' => -2]));
        $health->addAffliction($this->createPain($seriousWound, ['agilityMalus' => -3]));

        self::assertSame(-6, $health->getAgilityMalusFromAfflictions());
    }

    /**
     * @test
     */
    public function I_can_get_knack_malus_from_afflictions()
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(123));
        $seriousWound = $health->createWound($this->createWoundSize(70),
            SeriousWoundOriginCode::getPsychicalWoundOrigin(),
            $woundBoundary
        );
        $health->addAffliction($this->createPain($seriousWound, ['knackMalus' => -8]));
        $health->addAffliction($this->createAffliction($seriousWound, ['knackMalus' => -15]));
        $health->addAffliction($this->createPain($seriousWound, ['knackMalus' => -1]));

        self::assertSame(-24, $health->getKnackMalusFromAfflictions());
    }

    /**
     * @test
     */
    public function I_can_get_will_malus_from_afflictions()
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(123));
        $seriousWound = $health->createWound($this->createWoundSize(70),
            SeriousWoundOriginCode::getPsychicalWoundOrigin(),
            $woundBoundary
        );
        $health->addAffliction($this->createPain($seriousWound, ['willMalus' => -3]));
        $health->addAffliction($this->createAffliction($seriousWound, ['willMalus' => -2]));
        $health->addAffliction($this->createPain($seriousWound, ['willMalus' => -5]));

        self::assertSame(-10, $health->getWillMalusFromAfflictions());
    }

    /**
     * @test
     */
    public function I_can_get_intelligence_malus_from_afflictions()
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(123));
        $seriousWound = $health->createWound($this->createWoundSize(70),
            SeriousWoundOriginCode::getPsychicalWoundOrigin(),
            $woundBoundary
        );
        $health->addAffliction($this->createPain($seriousWound, ['intelligenceMalus' => 0]));
        $health->addAffliction($this->createAffliction($seriousWound, ['intelligenceMalus' => 1]));
        $health->addAffliction($this->createPain($seriousWound, ['intelligenceMalus' => 0]));
        $health->addAffliction($this->createPain($seriousWound, ['intelligenceMalus' => -6]));

        self::assertSame(-5, $health->getIntelligenceMalusFromAfflictions());
    }

    /**
     * @test
     */
    public function I_can_get_charisma_malus_from_afflictions()
    {
        $health = $this->createHealthToTest($woundBoundary = $this->createWoundBoundary(123));
        $seriousWound = $health->createWound($this->createWoundSize(70),
            SeriousWoundOriginCode::getPsychicalWoundOrigin(),
            $woundBoundary
        );
        $health->addAffliction($this->createPain($seriousWound, ['charismaMalus' => -5]));
        $health->addAffliction($this->createAffliction($seriousWound, ['charismaMalus' => -2]));

        self::assertSame(-7, $health->getCharismaMalusFromAfflictions());
    }

    /**
     * @test
     */
    public function I_can_be_glared()
    {
        $health = new Health();
        self::assertEquals(Glared::createWithoutGlare($health), $health->getGlared());
        $health->inflictByGlare($glare = $this->createGlare());
        self::assertEquals(Glared::createFromGlare($glare, $health), $health->getGlared());
        $previousGlared = $health->getGlared();
        $health->inflictByGlare($this->createGlare());
        self::assertNotSame($previousGlared, $health->getGlared());
    }

    /**
     * @param int $malus
     * @param bool $isShined
     * @return \Mockery\MockInterface|Glare
     */
    private function createGlare($malus = -123, $isShined = true)
    {
        $glare = $this->mockery(Glare::class);
        $glare->shouldReceive('getMalus')
            ->andReturn($malus);
        $glare->shouldReceive('isShined')
            ->andReturn($isShined);

        return $glare;
    }
}