<?php
namespace DrdPlus\Tests\Health\Afflictions;

use DrdPlus\Health\Afflictions\AfflictionByWound;
use DrdPlus\Health\Afflictions\AfflictionName;
use DrdPlus\Health\Afflictions\AfflictionProperty;
use DrdPlus\Health\Afflictions\AfflictionSize;
use DrdPlus\Health\Afflictions\AfflictionVirulence;
use DrdPlus\Health\Afflictions\Effects\AfflictionEffect;
use DrdPlus\Health\Afflictions\ElementalPertinence\ElementalPertinence;
use DrdPlus\Health\GridOfWounds;
use DrdPlus\Health\SeriousWound;
use DrdPlus\Properties\Derived\WoundBoundary;
use Granam\Tests\Tools\TestWithMockery;

abstract class AfflictionTest extends TestWithMockery
{

    /**
     * @test
     */
    abstract public function It_is_linked_with_health_immediately();

    /**
     * @test
     */
    abstract public function I_can_use_it();

    /**
     * @return \Mockery\MockInterface|AfflictionVirulence
     */
    protected function createAfflictionVirulence()
    {
        return $this->mockery(AfflictionVirulence::class);
    }

    /**
     * @return \Mockery\MockInterface|AfflictionProperty
     */
    protected function createAfflictionProperty()
    {
        return $this->mockery(AfflictionProperty::class);
    }

    /**
     * @param $value
     * @return \Mockery\MockInterface|AfflictionSize
     */
    protected function createAfflictionSize($value = null)
    {
        $afflictionSize = $this->mockery(AfflictionSize::class);
        if ($value !== null) {
            $afflictionSize->shouldReceive('getValue')
                ->andReturn($value);
        }

        return $afflictionSize;
    }

    /**
     * @return \Mockery\MockInterface|ElementalPertinence
     */
    protected function createElementalPertinence()
    {
        return $this->mockery(ElementalPertinence::class);
    }

    /**
     * @return \Mockery\MockInterface|AfflictionEffect
     */
    protected function createAfflictionEffect()
    {
        return $this->mockery(AfflictionEffect::class);
    }

    /**
     * @return \Mockery\MockInterface|\DateInterval
     */
    protected function createOutbreakPeriod()
    {
        return $this->mockery(\DateInterval::class);
    }

    /**
     * @return \Mockery\MockInterface|AfflictionName
     */
    protected function createAfflictionName()
    {
        return $this->mockery(AfflictionName::class);
    }

    /**
     * @param SeriousWound $wound
     * @param WoundBoundary $woundBoundary
     * @param int $filledHalfOfRows
     */
    protected function addSizeCalculation(SeriousWound $wound, WoundBoundary $woundBoundary, $filledHalfOfRows)
    {
        /** @var SeriousWound $wound */
        $health = $wound->getHealth();
        /** @var \Mockery\MockInterface $health */
        $health->shouldReceive('getGridOfWounds')
            ->andReturn($gridOfWounds = $this->mockery(GridOfWounds::class));
        $gridOfWounds->shouldReceive('calculateFilledHalfRowsFor')
            ->with($wound->getWoundSize(), $woundBoundary)
            ->andReturn($filledHalfOfRows);
    }

    /**
     * @test
     */
    public function I_get_will_intelligence_and_charisma_malus_zero_as_not_used()
    {
        $afflictionReflection = new \ReflectionClass(self::getSutClass());
        $afflictionConstructor = $afflictionReflection->getConstructor();
        $afflictionConstructor->setAccessible(true);

        /** @var AfflictionByWound $afflictionInstance */
        $afflictionInstance = $afflictionReflection->newInstanceWithoutConstructor();
        self::assertSame(0, $afflictionInstance->getWillMalus());
        self::assertSame(0, $afflictionInstance->getIntelligenceMalus());
        self::assertSame(0, $afflictionInstance->getCharismaMalus());
    }

    /**
     * @test
     */
    abstract public function I_can_get_heal_malus();

    /**
     * @test
     */
    abstract public function I_can_get_malus_to_activities();

    /**
     * @test
     */
    abstract public function I_can_get_strength_malus();

    /**
     * @test
     */
    abstract public function I_can_get_agility_malus();

    /**
     * @test
     */
    abstract public function I_can_get_knack_malus();

}