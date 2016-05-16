<?php
namespace DrdPlus\Tests\Person\Health\Afflictions\Effects;

use DrdPlus\Person\Health\Afflictions\AfflictionSize;
use DrdPlus\Person\Health\Afflictions\Effects\BleedingEffect;
use DrdPlus\Person\Health\Afflictions\SpecificAfflictions\Bleeding;
use DrdPlus\Person\Health\GridOfWounds;
use DrdPlus\Person\Health\Health;
use DrdPlus\Person\Health\Wound;
use DrdPlus\Person\Health\WoundOrigin;
use DrdPlus\Tables\Measurements\Wounds\Wounds as TableWounds;
use DrdPlus\Tables\Measurements\Wounds\WoundsBonus;
use DrdPlus\Tables\Measurements\Wounds\WoundsTable;

class BleedingEffectTest extends AfflictionEffectTest
{
    /**
     * @test
     */
    public function I_can_find_out_if_apply_even_on_success_against_trap()
    {
        $bleedingEffect = BleedingEffect::getIt();
        self::assertTrue($bleedingEffect->isEffectiveEvenOnSuccessAgainstTrap());
    }

    /**
     * @test
     */
    public function I_can_get_wound_caused_by_bleeding()
    {
        $bleedingEffect = BleedingEffect::getIt();
        $wound = $bleedingEffect->getWound($this->createBleeding(999 /* useless in this test */), $this->createWoundsTable(0 /* resulting wounds value */));
        self::assertFalse($wound, 'Expected no wound at all on zero wound value');

        $wound = $bleedingEffect->getWound(
            $this->createBleeding(
                0 /* bleeding size */,
                false /* not a serious injury */
            ),
            new WoundsTable()
        );
        self::assertInstanceOf(Wound::class, $wound);
        self::assertSame(2, $wound->getValue()); // 0 bleeding size ... some calculation ... see wounds table for details
        self::assertTrue($wound->getWoundOrigin()->isOrdinaryWoundOrigin()); // because not a serious injury

        $wound = $bleedingEffect->getWound(
            $this->createBleeding(
                20 /* bleeding size */,
                true /* serious injury */,
                $woundOrigin = WoundOrigin::getMechanicalCutWoundOrigin()
            ),
            new WoundsTable()
        );
        self::assertInstanceOf(Wound::class, $wound);
        self::assertSame(16, $wound->getValue()); // 20 bleeding size ... some calculation ... see wounds table for details
        self::assertSame($woundOrigin, $wound->getWoundOrigin());
    }

    /**
     * @param $size
     * @param bool $isSeriousInjury
     * @param WoundOrigin|null $bleedingWoundOrigin
     * @return \Mockery\MockInterface|Bleeding
     */
    private function createBleeding($size, $isSeriousInjury = false, WoundOrigin $bleedingWoundOrigin = null)
    {
        $bleeding = $this->mockery(Bleeding::class);
        $bleeding->shouldReceive('getSize')
            ->andReturn(AfflictionSize::getIt($size));
        $bleeding->shouldReceive('getWound')
            ->andReturn($woundCausedBleeding = $this->mockery(Wound::class));
        $woundCausedBleeding->shouldReceive('getHealth')
            ->andReturn($health = $this->mockery(Health::class));
        $health->shouldReceive('getGridOfWounds')
            ->andReturn($gridOfWounds = $this->mockery(GridOfWounds::class));
        $gridOfWounds->shouldReceive('isSeriousInjury')
            ->with(\Mockery::type('int'))
            ->andReturn($isSeriousInjury);
        if ($bleedingWoundOrigin !== null) {
            $woundCausedBleeding->shouldReceive('getWoundOrigin')
                ->andReturn($bleedingWoundOrigin);
        }

        return $bleeding;
    }

    /**
     * @param $woundsValue
     * @return \Mockery\MockInterface|WoundsTable
     */
    private function createWoundsTable($woundsValue)
    {
        $woundsTable = $this->mockery(WoundsTable::class);
        $woundsTable->shouldReceive('toWounds')
            ->with(\Mockery::type(WoundsBonus::class))
            ->andReturn($wounds = $this->mockery(TableWounds::class));
        $wounds->shouldReceive('getValue')
            ->andReturn($woundsValue);

        return $woundsTable;
    }
}
