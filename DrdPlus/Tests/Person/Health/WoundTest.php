<?php
namespace DrdPlus\Tests\Person\Health;

use DrdPlus\Person\Health\Health;
use DrdPlus\Person\Health\OrdinaryWoundOrigin;
use DrdPlus\Person\Health\PointOfWound;
use DrdPlus\Person\Health\SpecificWoundOrigin;
use DrdPlus\Person\Health\Wound;
use DrdPlus\Person\Health\WoundSize;
use Granam\Tests\Tools\TestWithMockery;

class WoundTest extends TestWithMockery
{
    /**
     * @test
     */
    public function I_can_use_it()
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $wound = new Wound(
            $health = $this->createHealth(),
            new WoundSize($woundSizeValue = 3),
            $ordinaryWoundOrigin = OrdinaryWoundOrigin::getIt()
        );
        self::assertNull($wound->getId());
        self::assertSame($health, $wound->getHealth());
        self::assertSame($woundSizeValue, $wound->getValue());
        self::assertFalse($wound->isSerious(), "Wound with {$ordinaryWoundOrigin} origin is not serious");
        self::assertSame($ordinaryWoundOrigin, $wound->getWoundOrigin());
        self::assertFalse($wound->isHealed(), "Wound with {$woundSizeValue} is not healed");
        $pointsOfWound = $wound->getPointsOfWound();
        self::assertCount($woundSizeValue, $pointsOfWound);
        foreach ($pointsOfWound as $pointOfWound) {
            self::assertInstanceOf(PointOfWound::class, $pointOfWound);
        }
        self::assertSame('3', (string)$wound);
    }

    /**
     * @return \Mockery\MockInterface|Health
     */
    private function createHealth()
    {
        return $this->mockery(Health::class);
    }

    /**
     * @test
     */
    public function I_can_heal_it_both_partially_and_fully()
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $wound = new Wound(
            $health = $this->createHealth(),
            new WoundSize($woundSizeValue = 3),
            $elementalWoundOrigin = SpecificWoundOrigin::getElementalWoundOrigin()
        );
        self::assertSame(3, $wound->getValue(), 'Expected same value as created with');
        self::assertCount(3, $wound->getPointsOfWound());
        self::assertFalse($wound->isHealed());
        self::assertTrue($wound->isSerious(), "Wound of {$elementalWoundOrigin} origin should be serious");

        self::assertSame(1, $wound->heal(1), 'Expected reported healed value to be 1');
        self::assertSame(2, $wound->getValue(), 'Expected one point of wound to be already healed');
        self::assertCount(2, $wound->getPointsOfWound());
        self::assertFalse($wound->isHealed());

        self::assertSame(2, $wound->heal(999), 'Expected reported healed value to be the remaining value, 2');
        self::assertEmpty($wound->getPointsOfWound());
        self::assertTrue($wound->isHealed());
    }

    /**
     * @test
     */
    public function I_can_create_both_light_and_serious_wound_with_zero_value()
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $ordinaryWound = new Wound(
            $this->createHealth(),
            new WoundSize(0),
            SpecificWoundOrigin::getMechanicalCrushWoundOrigin()
        );
        self::assertSame(0, $ordinaryWound->getValue());
        self::assertTrue($ordinaryWound->isHealed());

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $seriousWound = new Wound(
            $this->createHealth(),
            new WoundSize(0),
            OrdinaryWoundOrigin::getIt()
        );
        self::assertSame(0, $seriousWound->getValue());
        self::assertTrue($seriousWound->isHealed());
    }
}
