<?php
namespace DrdPlus\Tests\Person\Health;

use DrdPlus\Person\Health\GridOfWounds;
use DrdPlus\Person\Health\Health;
use DrdPlus\Person\Health\PointOfWound;
use DrdPlus\Person\Health\Wound;
use Granam\Tests\Tools\TestWithMockery;

class GridOfWoundsTest extends TestWithMockery
{
    /**
     * @var PointOfWound
     */
    private static $pointOfWound;

    protected function setUp()
    {
        self::$pointOfWound = $this->mockery(PointOfWound::class);
    }

    /**
     * @test
     */
    public function I_can_get_sum_of_wounds()
    {
        $gridOfWoundsWithoutWoundsAtAll = new GridOfWounds($this->createHealth([]));
        self::assertSame(0, $gridOfWoundsWithoutWoundsAtAll->getSumOfWounds());

        $gridOfWoundsWithHealedWoundsOnly = new GridOfWounds(
            $this->createHealth($this->createWounds($woundValues = [1, 345, 789]))
        );
        self::assertSame(array_sum($woundValues), $gridOfWoundsWithHealedWoundsOnly->getSumOfWounds());
    }

    /**
     * @param array|Wound[] $unhealedWounds
     * @param $woundsLimitValue
     * @return \Mockery\MockInterface|Health
     */
    private function createHealth(array $unhealedWounds = null, $woundsLimitValue = false)
    {
        $health = $this->mockery(Health::class);
        if ($unhealedWounds !== null) {
            $health->shouldReceive('getUnhealedWounds')
                ->andReturn($unhealedWounds);
        }
        if ($woundsLimitValue !== false) {
            $health->shouldReceive('getWoundsLimitValue')
                ->andReturn($woundsLimitValue);
        }

        return $health;
    }

    /**
     * @param array|int[] $woundValues
     * @return Wound[]
     */
    private function createWounds(array $woundValues)
    {
        $wounds = [];
        foreach ($woundValues as $woundValue) {
            $wound = $this->mockery(Wound::class);
            $wound->shouldReceive('getPointsOfWound')
                ->andReturn($this->createPointsOfWound($woundValue));

            $wounds[] = $wound;
        }

        return $wounds;
    }

    /**
     * @param int $woundValue
     * @return array|PointOfWound[]
     */
    private function createPointsOfWound($woundValue)
    {
        $pointsOfWound = [];
        for ($pointRank = 1; $pointRank <= $woundValue; $pointRank++) {
            $pointsOfWound[] = self::$pointOfWound;
        }

        return $pointsOfWound;
    }

    /**
     * @test
     */
    public function I_can_get_maximum_of_wounds_per_row()
    {
        $gridOfWoundsWithoutWoundsAtAll = new GridOfWounds($this->createHealth([] /* no wounds*/, $woundsLimitValue = 'foo'));
        self::assertSame($woundsLimitValue, $gridOfWoundsWithoutWoundsAtAll->getWoundsPerRowMaximum());
    }

    /**
     * @test
     */
    public function I_can_get_calculated_filled_half_rows_for_given_wound_value()
    {
        // limit of wounds divisible by two (odd)
        $gridOfWounds = new GridOfWounds($this->createHealth([] /* no wounds*/, 124));
        self::assertSame(6, $gridOfWounds->calculateFilledHalfRowsFor(492), 'Expected cap of half rows');

        $gridOfWounds = new GridOfWounds($this->createHealth([] /* no wounds*/, 124));
        self::assertSame(0, $gridOfWounds->calculateFilledHalfRowsFor(0), 'Expected no half row');

        $gridOfWounds = new GridOfWounds($this->createHealth([] /* no wounds*/, 22));
        self::assertSame(1, $gridOfWounds->calculateFilledHalfRowsFor(11), 'Expected two half rows');

        $gridOfWounds = new GridOfWounds($this->createHealth([] /* no wounds*/, 4));
        self::assertSame(5, $gridOfWounds->calculateFilledHalfRowsFor(10), 'Expected five half rows');

        // even limit of wounds
        $gridOfWounds = new GridOfWounds($this->createHealth([] /* no wounds*/, 111));
        self::assertSame(6, $gridOfWounds->calculateFilledHalfRowsFor(999), 'Expected cap of half rows');

        $gridOfWounds = new GridOfWounds($this->createHealth([] /* no wounds*/, 333));
        self::assertSame(0, $gridOfWounds->calculateFilledHalfRowsFor(5), 'Expected no half row');

        $gridOfWounds = new GridOfWounds($this->createHealth([] /* no wounds*/, 13));
        self::assertSame(0, $gridOfWounds->calculateFilledHalfRowsFor(6), '"first" half of row should be rounded up');

        $gridOfWounds = new GridOfWounds($this->createHealth([] /* no wounds*/, 13));
        self::assertSame(1, $gridOfWounds->calculateFilledHalfRowsFor(7));

        $gridOfWounds = new GridOfWounds($this->createHealth([] /* no wounds*/, 13));
        self::assertSame(2, $gridOfWounds->calculateFilledHalfRowsFor(13), 'Same value as row of wound should take two halves of such value even if even');

        $gridOfWounds = new GridOfWounds($this->createHealth([] /* no wounds*/, 5), '"third" half or row should be rounded up');
        self::assertSame(2, $gridOfWounds->calculateFilledHalfRowsFor(7));

        $gridOfWounds = new GridOfWounds($this->createHealth([] /* no wounds*/, 5));
        self::assertSame(3, $gridOfWounds->calculateFilledHalfRowsFor(8));

        $gridOfWounds = new GridOfWounds($this->createHealth([] /* no wounds*/, 5));
        self::assertSame(4, $gridOfWounds->calculateFilledHalfRowsFor(10));
    }

    /**
     * @test
     */
    public function I_can_find_out_if_wound_is_serious_by_its_value()
    {
        $gridOfWounds = new GridOfWounds($this->createHealth([] /* no wounds*/, 123));
        self::assertFalse($gridOfWounds->isSeriousInjury(61), 'Wound lesser than half of row should not be serious');
        self::assertTrue($gridOfWounds->isSeriousInjury(62), 'Wound same as half of row should be serious');
        self::assertTrue($gridOfWounds->isSeriousInjury(9999), 'Such big wound should be seriously serious');
    }

    /**
     * @test
     */
    public function I_can_get_maximum_of_health()
    {
        $gridOfWounds = new GridOfWounds($this->createHealth([] /* no wounds*/, 123));
        self::assertSame(369 /* 3 x 123 */, $gridOfWounds->getHealthMaximum());
    }

    /**
     * @test
     */
    public function I_can_get_remaining_health()
    {
        $gridOfWoundsWithoutWounds = new GridOfWounds($this->createHealth([] /* no wounds*/, $woundsLimit = 123));
        self::assertSame($woundsLimit * 3, $gridOfWoundsWithoutWounds->getRemainingHealth());

        $gridOfWoundsWithWounds = new GridOfWounds($this->createHealth($this->createWounds($wounds = [11, 65, 42]), $woundsLimit = 456));
        self::assertSame($woundsLimit * 3 - array_sum($wounds), $gridOfWoundsWithWounds->getRemainingHealth());
    }

    /**
     * @test
     */
    public function I_can_get_number_of_filled_rows()
    {
        $gridOfWounds = new GridOfWounds($this->createHealth($this->createWounds([1, 21, 5, 14]), 23));
        self::assertSame(1, $gridOfWounds->getNumberOfFilledRows());

        $gridOfWounds = new GridOfWounds($this->createHealth($this->createWounds([1, 21, 10, 14]), 23));
        self::assertSame(2, $gridOfWounds->getNumberOfFilledRows());

        $gridOfWounds = new GridOfWounds($this->createHealth($this->createWounds([1, 21, 10, 14, 500]), 23));
        self::assertSame(3, $gridOfWounds->getNumberOfFilledRows(), 'Maximum of rows should not exceed 3');
    }
}
