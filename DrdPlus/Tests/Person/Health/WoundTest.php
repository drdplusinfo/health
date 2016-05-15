<?php
namespace DrdPlus\Tests\Person\Health;

use DrdPlus\Person\Health\Health;
use DrdPlus\Person\Health\PointOfWound;
use DrdPlus\Person\Health\Wound;
use DrdPlus\Person\Health\WoundOrigin;
use DrdPlus\Person\Health\WoundSize;
use Granam\Tests\Tools\TestWithMockery;

class WoundTest extends TestWithMockery
{
    /**
     * @test
     */
    public function I_can_use_it()
    {
        $wound = new Wound(
            $health = $this->createHealth(),
            new WoundSize($woundSizeValue = 3),
            $woundOrigin = WoundOrigin::getOrdinaryWoundOrigin()
        );
        self::assertNull($wound->getId());
        self::assertSame($health, $wound->getHealth());
        self::assertSame($woundSizeValue, $wound->getValue());
        self::assertFalse($wound->isSerious(), "Wound with {$woundOrigin} origin is not serious");
        self::assertSame($woundOrigin, $wound->getWoundOrigin());
        self::assertFalse($wound->isHealed(), "Wound with {$woundSizeValue} is not healed");
        $pointsOfWound = $wound->getPointsOfWound();
        self::assertCount($woundSizeValue, $pointsOfWound);
        foreach ($pointsOfWound as $pointOfWound) {
            self::assertInstanceOf(PointOfWound::class, $pointOfWound);
        }
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
        $wound = new Wound(
            $health = $this->createHealth(),
            new WoundSize($woundSizeValue = 3),
            $woundOrigin = WoundOrigin::getElementalWoundOrigin()
        );
        self::assertSame(3, $wound->getValue(), 'Expected same value as created with');
        self::assertCount(3, $wound->getPointsOfWound());
        self::assertFalse($wound->isHealed());
        self::assertTrue($wound->isSerious(), "Wound of {$woundOrigin} origin should be serious");

        self::assertSame(1, $wound->heal(1), 'Expected reported healed value to be 1');
        self::assertSame(2, $wound->getValue(), 'Expected one point of wound to be already healed');
        self::assertCount(2, $wound->getPointsOfWound());
        self::assertFalse($wound->isHealed());

        self::assertSame(2, $wound->heal(999), 'Expected reported healed value to be the remaining value, 2');
        self::assertEmpty($wound->getPointsOfWound());
        self::assertTrue($wound->isHealed());
    }
}
