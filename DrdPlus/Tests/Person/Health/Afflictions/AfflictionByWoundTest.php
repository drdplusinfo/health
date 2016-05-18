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
        /* $affliction = $this->createAffliction(
            $wound = $this->createWound($health = $this->createHealth())
        );
            $domain = $this->createAfflictionDomain(),
            $virulence = $this->createAfflictionVirulence(),
            $source = $this->createAfflictionSource(),
            $property = $this->createAfflictionProperty(),
            $dangerousness = $this->createAfflictionDangerousness(),
            $size = $this->createAfflictionSize(),
            $elementalPertinence = $this->createElementalPertinence(),
            $effect = $this->createAfflictionEffect(),
            $outbreakPeriod = $this->createOutbreakPeriod(),
            $afflictionName = $this->createAfflictionName()
        self::assertNull($affliction->getId());
        self::assertSame($wound, $affliction->getWound());
        self::assertSame($health, $affliction->getHealth());

        return $affliction;

        self::assertSame($domain, $affliction->getDomain());
        self::assertSame($virulence, $affliction->getVirulence());
        self::assertSame($source, $affliction->getSource());
        self::assertSame($property, $affliction->getProperty());
        self::assertSame($dangerousness, $affliction->getDangerousness());
        self::assertSame($size, $affliction->getSize());
        self::assertSame($elementalPertinence, $affliction->getElementalPertinence());
        self::assertSame($effect, $affliction->getEffect());
        self::assertSame($outbreakPeriod, $affliction->getOutbreakPeriod());
        self::assertSame($afflictionName, $affliction->getAfflictionName());
    }
*/

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
    private function createAfflictionDomain()
    {
        return $this->mockery(AfflictionDomain::class);
    }

    /**
     * @return \Mockery\MockInterface|AfflictionVirulence
     */
    private function createAfflictionVirulence()
    {
        return $this->mockery(AfflictionVirulence::class);
    }

    /**
     * @return \Mockery\MockInterface|AfflictionSource
     */
    private function createAfflictionSource()
    {
        return $this->mockery(AfflictionSource::class);
    }

    /**
     * @return \Mockery\MockInterface|AfflictionProperty
     */
    private function createAfflictionProperty()
    {
        return $this->mockery(AfflictionProperty::class);
    }

    /**
     * @return \Mockery\MockInterface|AfflictionDangerousness
     */
    private function createAfflictionDangerousness()
    {
        return $this->mockery(AfflictionDangerousness::class);
    }

    /**
     * @return \Mockery\MockInterface|AfflictionSize
     */
    private function createAfflictionSize()
    {
        return $this->mockery(AfflictionSize::class);
    }

    /**
     * @return \Mockery\MockInterface|ElementalPertinence
     */
    private function createElementalPertinence()
    {
        return $this->mockery(ElementalPertinence::class);
    }

    /**
     * @return \Mockery\MockInterface|AfflictionEffect
     */
    private function createAfflictionEffect()
    {
        return $this->mockery(AfflictionEffect::class);
    }

    /**
     * @return \Mockery\MockInterface|\DateInterval
     */
    private function createOutbreakPeriod()
    {
        return $this->mockery(\DateInterval::class);
    }

    /**
     * @return \Mockery\MockInterface|AfflictionName
     */
    private function createAfflictionName()
    {
        return $this->mockery(AfflictionName::class);
    }
}
