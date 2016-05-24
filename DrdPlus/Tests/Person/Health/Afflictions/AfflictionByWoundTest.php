<?php
namespace DrdPlus\Tests\Person\Health\Afflictions;

use DrdPlus\Person\Health\Afflictions\AfflictionByWound;
use DrdPlus\Person\Health\Afflictions\AfflictionDangerousness;
use DrdPlus\Person\Health\Afflictions\AfflictionDomain;
use DrdPlus\Person\Health\Afflictions\AfflictionName;
use DrdPlus\Person\Health\Afflictions\AfflictionProperty;
use DrdPlus\Person\Health\Afflictions\AfflictionSize;
use DrdPlus\Person\Health\Afflictions\AfflictionSource;
use DrdPlus\Person\Health\Afflictions\AfflictionVirulence;
use DrdPlus\Person\Health\Afflictions\Effects\AfflictionEffect;
use DrdPlus\Person\Health\Afflictions\ElementalPertinence\ElementalPertinence;
use DrdPlus\Person\Health\Health;
use DrdPlus\Person\Health\SpecificWoundOrigin;
use DrdPlus\Person\Health\Wound;
use DrdPlus\Person\Health\WoundOrigin;
use Granam\Tests\Tools\TestWithMockery;

abstract class AfflictionByWoundTest extends TestWithMockery
{
    /**
     * @test
     * @expectedException \DrdPlus\Person\Health\Afflictions\Exceptions\WoundHasToBeSeriousForAffliction
     */
    public function I_can_not_create_it_with_non_serious_wound()
    {
        $reflection = new \ReflectionClass($this->getSutClass());
        $constructor = $reflection->getConstructor();
        $constructor->setAccessible(true);

        $instance = $reflection->newInstanceWithoutConstructor();
        $constructor->invoke(
            $instance,
            $this->createWound(false /* not serious */),
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
     * @return string|AfflictionByWound
     */
    private function getSutClass()
    {
        return preg_replace('~[\\\]Tests([\\\].+)Test$~', '$1', static::class);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Person\Health\Afflictions\Exceptions\WoundHasToBeFreshForAffliction
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
    abstract public function I_can_use_it();

    /**
     * @param bool $isSerious
     * @param bool $isOld
     * @param $value
     * @param WoundOrigin $woundOrigin
     * @return \Mockery\MockInterface|Wound
     */
    protected function createWound($isSerious = true, $isOld = false, $value = 0, WoundOrigin $woundOrigin = null)
    {
        $wound = $this->mockery(Wound::class);
        $wound->shouldReceive('getHealth')
            ->andReturn($this->mockery(Health::class));
        $wound->shouldReceive('isSerious')
            ->andReturn($isSerious);
        $wound->shouldReceive('isOld')
            ->andReturn($isOld);
        $wound->shouldReceive('getValue')
            ->andReturn($value);
        $wound->shouldReceive('__toString')
            ->andReturn((string)$value);
        $wound->shouldReceive('getWoundOrigin')
            ->andReturn($woundOrigin ?: SpecificWoundOrigin::getElementalWoundOrigin());

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
