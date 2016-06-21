<?php
namespace DrdPlus\Tests\Health\Afflictions;

use DrdPlus\Health\Afflictions\AfflictionByWound;
use DrdPlus\Health\Afflictions\AfflictionDangerousness;
use DrdPlus\Health\Afflictions\AfflictionDomain;
use DrdPlus\Health\Afflictions\AfflictionName;
use DrdPlus\Health\Afflictions\AfflictionProperty;
use DrdPlus\Health\Afflictions\AfflictionSize;
use DrdPlus\Health\Afflictions\AfflictionSource;
use DrdPlus\Health\Afflictions\AfflictionVirulence;
use DrdPlus\Health\Afflictions\Effects\AfflictionEffect;
use DrdPlus\Health\Afflictions\ElementalPertinence\ElementalPertinence;
use DrdPlus\Health\Health;
use DrdPlus\Health\OrdinaryWound;
use DrdPlus\Health\SeriousWound;
use DrdPlus\Health\SeriousWoundOrigin;
use DrdPlus\Health\WoundOrigin;
use DrdPlus\Health\WoundSize;
use DrdPlus\Properties\Derived\WoundBoundary;
use Granam\Tests\Tools\TestWithMockery;

abstract class AfflictionByWoundTest extends TestWithMockery
{

    /**
     * @return string|AfflictionByWound
     */
    private function getSutClass()
    {
        return preg_replace('~[\\\]Tests([\\\].+)Test$~', '$1', static::class);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Health\Afflictions\Exceptions\WoundHasToBeFreshForAffliction
     */
    public function I_can_not_create_it_with_old_wound()
    {
        $reflection = new \ReflectionClass($this->getSutClass());
        $constructor = $reflection->getConstructor();
        $constructor->setAccessible(true);

        $instance = $reflection->newInstanceWithoutConstructor();
        $constructor->invoke(
            $instance,
            $this->createWound(true /* serious */, true /* old */),
            $this->mockery(AfflictionDomain::class),
            $this->mockery(AfflictionVirulence::class),
            $this->mockery(AfflictionSource::class),
            $this->mockery(AfflictionProperty::class),
            $this->mockery(AfflictionDangerousness::class),
            $this->mockery(AfflictionSize::class),
            $this->mockery(ElementalPertinence::class),
            $this->mockery(AfflictionEffect::class),
            $this->mockery(\DateInterval::class),
            $this->mockery(AfflictionName::class)
        );
    }

    /**
     * @test
     */
    public function It_is_linked_with_health_immediately()
    {
        $woundBoundary = $this->mockery(WoundBoundary::class);
        $woundBoundary->shouldReceive('getValue')
            ->andReturn(5);
        /** @var WoundBoundary $woundBoundary */
        $health = new Health($woundBoundary);
        $woundSize = $this->mockery(WoundSize::class);
        $woundSize->shouldReceive('getValue')
            ->andReturn(5);
        /** @var WoundSize $woundSize */
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $seriousWound = $health->createWound($woundSize, SeriousWoundOrigin::getMechanicalCutWoundOrigin());
        $afflictionReflection = new \ReflectionClass($this->getSutClass());
        $afflictionConstructor = $afflictionReflection->getConstructor();
        $afflictionConstructor->setAccessible(true);

        $afflictionInstance = $afflictionReflection->newInstanceWithoutConstructor();
        $afflictionConstructor->invoke(
            $afflictionInstance,
            $seriousWound,
            $this->mockery(AfflictionDomain::class),
            $this->mockery(AfflictionVirulence::class),
            $this->mockery(AfflictionSource::class),
            $this->mockery(AfflictionProperty::class),
            $this->mockery(AfflictionDangerousness::class),
            $this->mockery(AfflictionSize::class),
            $this->mockery(ElementalPertinence::class),
            $this->mockery(AfflictionEffect::class),
            $this->mockery(\DateInterval::class),
            $this->mockery(AfflictionName::class)
        );

        self::assertSame([$afflictionInstance], $health->getAfflictions()->toArray());
    }

    /**
     * @test
     */
    abstract public function I_can_use_it();

    /**
     * @param bool $isSerious
     * @param bool $isOld
     * @param $value
     * @param WoundOrigin $woundOrigin
     * @return \Mockery\MockInterface|SeriousWound|OrdinaryWound
     */
    protected function createWound($isSerious = true, $isOld = false, $value = 0, WoundOrigin $woundOrigin = null)
    {
        $wound = $this->mockery($isSerious ? SeriousWound::class : OrdinaryWound::class);
        $wound->shouldReceive('getHealth')
            ->andReturn($health = $this->mockery(Health::class));
        $health->shouldReceive('addAffliction')
            ->with(\Mockery::type($this->getSutClass()));
        $wound->shouldReceive('isSerious')
            ->andReturn($isSerious);
        $wound->shouldReceive('isOld')
            ->andReturn($isOld);
        $wound->shouldReceive('getValue')
            ->andReturn($value);
        $wound->shouldReceive('__toString')
            ->andReturn((string)$value);
        $wound->shouldReceive('getWoundOrigin')
            ->andReturn($woundOrigin ?: SeriousWoundOrigin::getElementalWoundOrigin());

        return $wound;
    }

    /**
     * @return \Mockery\MockInterface|AfflictionDomain
     */
    protected function createAfflictionDomain()
    {
        return $this->mockery(AfflictionDomain::class);
    }

    /**
     * @return \Mockery\MockInterface|AfflictionVirulence
     */
    protected function createAfflictionVirulence()
    {
        return $this->mockery(AfflictionVirulence::class);
    }

    /**
     * @return \Mockery\MockInterface|AfflictionSource
     */
    protected function createAfflictionSource()
    {
        return $this->mockery(AfflictionSource::class);
    }

    /**
     * @return \Mockery\MockInterface|AfflictionProperty
     */
    protected function createAfflictionProperty()
    {
        return $this->mockery(AfflictionProperty::class);
    }

    /**
     * @return \Mockery\MockInterface|AfflictionDangerousness
     */
    protected function createAfflictionDangerousness()
    {
        return $this->mockery(AfflictionDangerousness::class);
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
}
