<?php
namespace DrdPlus\Tests\Health;

use Drd\DiceRoll\Templates\Rollers\SpecificRolls\Roll2d6DrdPlus;
use DrdPlus\Codes\ActivityAffectingHealingCode;
use DrdPlus\Codes\ConditionsAffectingHealingCode;
use DrdPlus\Codes\RaceCode;
use DrdPlus\Codes\SubRaceCode;
use DrdPlus\Health\HealingPower;
use DrdPlus\Properties\Derived\Toughness;
use DrdPlus\Tables\Body\Healing\HealingByActivityTable;
use DrdPlus\Tables\Body\Healing\HealingByConditionsTable;
use DrdPlus\Tables\Body\Healing\HealingConditionsPercents;
use DrdPlus\Tables\Measurements\Wounds\Wounds;
use DrdPlus\Tables\Measurements\Wounds\WoundsBonus;
use DrdPlus\Tables\Measurements\Wounds\WoundsTable;
use DrdPlus\Tables\Races\RacesTable;
use Granam\Tests\Tools\TestWithMockery;

class HealingPowerTest extends TestWithMockery
{
    /**
     * @test
     */
    public function I_can_use_it_for_treatment()
    {
        $healingPower = HealingPower::createForTreatment(123, $this->createWoundsTable(123, 987));
        self::assertSame(123, $healingPower->getValue());
        self::assertSame('123', (string)$healingPower);
        self::assertSame(990, $healingPower->getHealUpTo($this->createToughness(3)));
    }

    /**
     * @param $expectedWoundsBonus
     * @param $returnWoundsValue
     * @return \Mockery\MockInterface|WoundsTable
     */
    private function createWoundsTable($expectedWoundsBonus, $returnWoundsValue)
    {
        $woundsTable = $this->mockery(WoundsTable::class);
        $woundsTable->shouldReceive('toWounds')
            ->andReturnUsing(function (WoundsBonus $woundBonus) use ($expectedWoundsBonus, $returnWoundsValue) {
                self::assertSame($expectedWoundsBonus, $woundBonus->getValue());
                $wounds = $this->mockery(Wounds::class);
                $wounds->shouldReceive('getValue')
                    ->andReturn($returnWoundsValue);
                $wounds->shouldReceive('getBonus')
                    ->andReturn($woundBonus);

                return $wounds;
            });

        return $woundsTable;
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
     * @test
     */
    public function I_can_use_it_for_regeneration()
    {
        foreach ([true, false] as $hasNativeRegeneration) {
            $healingPower = HealingPower::createForRegeneration(
                $this->createRaceCode('foo'),
                $this->createSubRaceCode('bar'),
                $this->createRacesTable('foo', 'bar', $hasNativeRegeneration),
                $this->createActivityCode('baz'),
                $this->createHealingByActivityTable('baz', 123),
                $this->createConditionCode('qux'),
                $healingConditionsPercents = $this->createHealingConditionsPercents(),
                $this->createHealingByConditionsTable('qux', $healingConditionsPercents, 456),
                $this->createRoll2d6(789),
                $this->createWoundsTable($expectedValue = -7 + 123 + 456 + 789 + ($hasNativeRegeneration ? +4 : 0), 112233)
            );
            self::assertSame($expectedValue, $healingPower->getValue());
            self::assertSame((string)$expectedValue, (string)$healingPower);
            self::assertSame(112236, $healingPower->getHealUpTo($this->createToughness(3)));
        }
    }

    /**
     * @param $value
     * @return \Mockery\MockInterface|RaceCode
     */
    private function createRaceCode($value)
    {
        $raceCode = $this->mockery(RaceCode::class);
        $raceCode->shouldReceive('getValue')
            ->andReturn($value);

        return $raceCode;
    }

    /**
     * @param $value
     * @return \Mockery\MockInterface|SubRaceCode
     */
    private function createSubRaceCode($value)
    {
        $subRaceCode = $this->mockery(SubRaceCode::class);
        $subRaceCode->shouldReceive('getValue')
            ->andReturn($value);

        return $subRaceCode;
    }

    /**
     * @param $expectedRaceCode
     * @param $expectedSubRaceCode
     * @param $hasNativeRegeneration
     * @return RacesTable
     */
    private function createRacesTable($expectedRaceCode, $expectedSubRaceCode, $hasNativeRegeneration)
    {
        $racesTable = $this->mockery(RacesTable::class);
        $racesTable->shouldReceive('hasNativeRegeneration')
            ->with($expectedRaceCode, $expectedSubRaceCode)
            ->andReturn($hasNativeRegeneration);

        return $racesTable;
    }

    /**
     * @param $value
     * @return \Mockery\MockInterface|ActivityAffectingHealingCode
     */
    private function createActivityCode($value)
    {
        $activityCode = $this->mockery(ActivityAffectingHealingCode::class);
        $activityCode->shouldReceive('getValue')
            ->andReturn($value);

        return $activityCode;
    }

    /**
     * @param $expectedActivity
     * @param $bonus
     * @return \Mockery\MockInterface|HealingByActivityTable
     */
    private function createHealingByActivityTable($expectedActivity, $bonus)
    {
        $healingByActivityTable = $this->mockery(HealingByActivityTable::class);
        $healingByActivityTable->shouldReceive('getHealingBonusByActivity')
            ->with($expectedActivity)
            ->andReturn($bonus);

        return $healingByActivityTable;
    }

    /**
     * @param $value
     * @return \Mockery\MockInterface|ConditionsAffectingHealingCode
     */
    private function createConditionCode($value)
    {
        $conditionsCode = $this->mockery(ConditionsAffectingHealingCode::class);
        $conditionsCode->shouldReceive('getValue')
            ->andReturn($value);

        return $conditionsCode;
    }

    /**
     * @return HealingConditionsPercents
     */
    private function createHealingConditionsPercents()
    {
        return $this->mockery(HealingConditionsPercents::class);
    }

    /**
     * @param $conditions
     * @param $percents
     * @param $healingByConditions
     * @return \Mockery\MockInterface|HealingByConditionsTable
     */
    private function createHealingByConditionsTable($conditions, $percents, $healingByConditions)
    {
        $healingByConditionsTable = $this->mockery(HealingByConditionsTable::class);
        $healingByConditionsTable->shouldReceive('getHealingBonusByConditions')
            ->with($conditions, $percents)
            ->andReturn($healingByConditions);

        return $healingByConditionsTable;
    }

    /**
     * @param $value
     * @return \Mockery\MockInterface|Roll2d6DrdPlus
     */
    private function createRoll2d6($value)
    {
        $roll2d6 = $this->mockery(Roll2d6DrdPlus::class);
        $roll2d6->shouldReceive('getValue')
            ->andReturn($value);

        return $roll2d6;
    }

    /**
     * @test
     */
    public function I_can_decrease_power_by_already_healed_amount()
    {
        $healingPower = HealingPower::createForTreatment(123, $woundsTable = $this->createWoundsTable(123, 987));
        self::assertSame(123, $healingPower->getValue());

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $notDecreased = $healingPower->decreaseByHealedAmount(0, $this->createToughness(456), $woundsTable);
        self::assertSame($healingPower, $notDecreased, 'It should be the very same instance if no change happened at all');

        $woundsTable->shouldReceive('toBonus')
            ->andReturnUsing(function (Wounds $wounds) {
                self::assertSame(900 /* 987 - 87 */, $wounds->getValue(), 'Expected original heal-up-to decreased by healed amount');

                $woundBonus = $this->mockery(WoundsBonus::class);
                $woundBonus->shouldReceive('getValue')
                    ->andReturn(333);

                return $woundBonus;
            });
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $decreased = $healingPower->decreaseByHealedAmount(87, $this->createToughness(11), $woundsTable);
        self::assertNotEquals($healingPower, $decreased, 'It should not has same value nor be the same instance');
        self::assertSame(333, $decreased->getValue());
    }

    /**
     * @test
     * @expectedException \DrdPlus\Health\Exceptions\HealedAmountIsTooBig
     */
    public function I_can_not_get_new_instance_by_strangely_high_healed_amount()
    {
        $healingPower = HealingPower::createForTreatment(123, $woundsTable = $this->createWoundsTable(123, 10));
        $healingPower->decreaseByHealedAmount(12, $this->createToughness(1), $woundsTable);
    }

    /**
     * @test
     */
    public function I_can_continually_spent_all_the_healing_power()
    {
        $healingPower = HealingPower::createForTreatment(26, $woundsTable = new WoundsTable());
        self::assertSame(26, $healingPower->getValue());
        self::assertSame(66, $healingPower->getHealUpTo($toughness = $this->createToughness(3)));
        while ($healingPower->getHealUpTo($toughness = $this->createToughness(3)) > 0) {
            $previousHealUpTo = $healingPower->getHealUpTo($toughness);
            $healingPower = $healingPower->decreaseByHealedAmount(1, $toughness, $woundsTable);
            self::assertSame(
                $previousHealUpTo - 1,
                $healingPower->getHealUpTo($toughness),
                "Expected new 'heal up to' to be one less than previous $previousHealUpTo"
            );
        }
    }
}
