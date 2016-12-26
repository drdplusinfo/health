<?php
namespace DrdPlus\Health\LightInflictions;

use DrdPlus\Tables\Measurements\Amount\Amount;
use DrdPlus\Tables\Measurements\Amount\AmountBonus;
use DrdPlus\Tables\Measurements\Amount\AmountTable;
use DrdPlus\Tables\Measurements\Distance\Distance;
use DrdPlus\Tables\Measurements\Distance\DistanceTable;
use Granam\Integer\IntegerObject;
use Granam\Tests\Tools\TestWithMockery;

class OpacityTest extends TestWithMockery
{
    /**
     * @test
     * @dataProvider provideDensityDistanceAndExpectedOpacity
     * @param int $density
     * @param int $distanceInMeters
     * @param int $expectedOpacity
     */
    public function I_can_get_opacity_from_barrier_density($density, $distanceInMeters, $expectedOpacity)
    {
        $opacity = Opacity::createFromBarrierDensity(
            new IntegerObject($density),
            new Distance($distanceInMeters, Distance::M, new DistanceTable()),
            new AmountTable()
        );
        self::assertSame($expectedOpacity, $opacity->getValue());
        self::assertSame((string)$expectedOpacity, (string)$opacity);
    }

    public function provideDensityDistanceAndExpectedOpacity()
    {
        return [
            [10, 3, 10], // note: there is a mistake in PPH on page 129, left column - distance bonus for 3 meters is probably taken lower (2) instead of higher (3)
            [10, 2, 6],
            [1, 1, 1],
        ];
    }

    /**
     * @test
     */
    public function I_can_get_zero_opacity_as_transparent()
    {
        $transparentOpacity = Opacity::createTransparent();
        self::assertSame(0, $transparentOpacity->getValue());
    }

    /**
     * @test
     */
    public function I_can_get_malus_to_an_item_visibility()
    {
        $transparentOpacity = Opacity::createTransparent();
        self::assertSame(0, $transparentOpacity->getVisibilityMalus());

        /** @var \Mockery\MockInterface|AmountTable $cheatingAmountTable */
        $cheatingAmountTable = $this->mockery(AmountTable::class);
        $cheatingAmountTable->shouldReceive('toAmount')
            ->with($this->type(AmountBonus::class))
            ->andReturn(new Amount(-1, Amount::AMOUNT, new AmountTable()));

        $negativeOpacity = Opacity::createFromBarrierDensity(
            new IntegerObject(123),
            new Distance(1, Distance::M, new DistanceTable()),
            $cheatingAmountTable
        );
        self::assertLessThan(0, $negativeOpacity->getValue());
        self::assertSame(0, $negativeOpacity->getVisibilityMalus(), 'Zero malus expected for negative opacity');

        $positiveOpacity = Opacity::createFromBarrierDensity(
            new IntegerObject(10),
            new Distance(5, Distance::M, new DistanceTable()),
            new AmountTable()
        );
        self::assertGreaterThan(0, $positiveOpacity->getValue());
        self::assertSame(-16, $positiveOpacity->getVisibilityMalus());
    }
}