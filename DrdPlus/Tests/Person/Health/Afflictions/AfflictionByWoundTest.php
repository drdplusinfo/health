<?php
namespace DrdPlus\Tests\Person\Health\Afflictions;

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
use DrdPlus\Person\Health\Wound;
use Granam\Tests\Tools\TestWithMockery;

abstract class AfflictionByWoundTest extends TestWithMockery
{
    /**
     * @test
     */
    abstract public function I_can_use_it();

    /**
     * @return \Mockery\MockInterface|Wound
     */
    protected function createWound()
    {
        $wound = $this->mockery(Wound::class);
        $wound->shouldReceive('getHealth')
            ->andReturn($this->mockery(Health::class));

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
