<?php
namespace DrdPlus\Tests\Person\Health;

use DrdPlus\Person\Health\PointOfWound;
use DrdPlus\Person\Health\Wound;
use Granam\Tests\Tools\TestWithMockery;

class PointOfWoundTest extends TestWithMockery
{
    /**
     * @test
     */
    public function I_can_use_it()
    {
        $pointOfWound = new PointOfWound($wound = $this->createWound());
        self::assertNull($pointOfWound->getId());
        self::assertSame(1, $pointOfWound->getValue());
        self::assertSame($wound, $pointOfWound->getWound());
    }

    /**
     * @return \Mockery\MockInterface|Wound
     */
    private function createWound()
    {
        return $this->mockery(Wound::class);
    }
}
