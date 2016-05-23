<?php
namespace DrdPlus\Tests\Person\Health;

use DrdPlus\Person\Health\HealingPower;
use DrdPlus\Tables\Measurements\Wounds\Wounds;
use DrdPlus\Tables\Measurements\Wounds\WoundsBonus;
use DrdPlus\Tables\Measurements\Wounds\WoundsTable;
use Granam\Tests\Tools\TestWithMockery;

class HealingPowerTest extends TestWithMockery
{
    /**
     * @test
     */
    public function I_can_use_it()
    {
        $healingPower = new HealingPower(123, $this->createWoundsTable(987));
        self::assertSame(123, $healingPower->getValue());
        self::assertSame('123', (string)$healingPower);
        self::assertSame(987, $healingPower->getHealUpTo());
    }

    /**
     * @param $woundsValue
     * @return \Mockery\MockInterface|WoundsTable
     */
    private function createWoundsTable($woundsValue)
    {
        $woundsTable = $this->mockery(WoundsTable::class);
        $woundsTable->shouldReceive('toWounds')
            ->andReturnUsing(function (WoundsBonus $woundBonus) use ($woundsValue) {
                self::assertSame(123, $woundBonus->getValue());
                $wounds = $this->mockery(Wounds::class);
                $wounds->shouldReceive('getValue')
                    ->andReturn($woundsValue);

                return $wounds;
            });

        return $woundsTable;
    }
}
