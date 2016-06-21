<?php
namespace DrdPlus\Tests\Health\Afflictions\SpecificAfflictions;

use DrdPlus\Codes\PropertyCode;
use DrdPlus\Health\Afflictions\AfflictionDangerousness;
use DrdPlus\Health\Afflictions\AfflictionDomain;
use DrdPlus\Health\Afflictions\AfflictionName;
use DrdPlus\Health\Afflictions\AfflictionProperty;
use DrdPlus\Health\Afflictions\AfflictionSize;
use DrdPlus\Health\Afflictions\AfflictionSource;
use DrdPlus\Health\Afflictions\AfflictionVirulence;
use DrdPlus\Health\Afflictions\Effects\BleedingEffect;
use DrdPlus\Health\Afflictions\ElementalPertinence\WaterPertinence;
use DrdPlus\Health\Afflictions\SpecificAfflictions\Bleeding;
use DrdPlus\Health\GridOfWounds;
use DrdPlus\Health\Wound;
use DrdPlus\Tests\Health\Afflictions\AfflictionByWoundTest;

class BleedingTest extends AfflictionByWoundTest
{
    /**
     * @test
     */
    public function I_can_use_it()
    {
        $wound = $this->createWound();
        $this->addSizeCalculation($wound, $filledHalfOfRows = 123);
        $bleeding = Bleeding::createIt($wound);

        self::assertNull($bleeding->getId());
        self::assertSame($wound, $bleeding->getSeriousWound());

        self::assertInstanceOf(AfflictionDomain::class, $bleeding->getDomain());
        self::assertSame('physical', $bleeding->getDomain()->getValue());

        self::assertInstanceOf(AfflictionVirulence::class, $bleeding->getVirulence());
        self::assertSame(AfflictionVirulence::ROUND, $bleeding->getVirulence()->getValue());

        self::assertInstanceOf(AfflictionSource::class, $bleeding->getSource());
        self::assertSame(AfflictionSource::ACTIVE, $bleeding->getSource()->getValue());

        self::assertInstanceOf(AfflictionProperty::class, $bleeding->getProperty());
        self::assertSame(PropertyCode::TOUGHNESS, $bleeding->getProperty()->getValue());

        self::assertInstanceOf(AfflictionDangerousness::class, $bleeding->getDangerousness());
        self::assertSame(15, $bleeding->getDangerousness()->getValue());

        self::assertInstanceOf(AfflictionSize::class, $bleeding->getSize());
        self::assertSame($filledHalfOfRows - 1, $bleeding->getSize()->getValue());

        self::assertInstanceOf(WaterPertinence::class, $bleeding->getElementalPertinence());
        self::assertTrue($bleeding->getElementalPertinence()->isMinus());

        self::assertInstanceOf(BleedingEffect::class, $bleeding->getEffect());

        self::assertInstanceOf(\DateInterval::class, $bleeding->getOutbreakPeriod());
        self::assertSame('0y0m0d0h0i0s', $bleeding->getOutbreakPeriod()->format('%yy%mm%dd%hh%ii%ss'));

        self::assertInstanceOf(AfflictionName::class, $bleeding->getName());
        self::assertSame('bleeding', $bleeding->getName()->getValue());
    }

    /**
     * @param Wound $wound
     * @param int $filledHalfOfRows
     */
    private function addSizeCalculation(Wound $wound, $filledHalfOfRows)
    {
        /** @var Wound $wound */
        $health = $wound->getHealth();
        /** @var \Mockery\MockInterface $health */
        $health->shouldReceive('getGridOfWounds')
            ->andReturn($gridOfWounds = $this->mockery(GridOfWounds::class));
        $gridOfWounds->shouldReceive('calculateFilledHalfRowsFor')
            ->with($wound->getValue())
            ->andReturn($filledHalfOfRows);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Health\Afflictions\SpecificAfflictions\Exceptions\BleedingCanNotExistsDueToTooLowWound
     */
    public function I_can_not_create_it_from_too_low_wound()
    {
        $wound = $this->createWound();
        $this->addSizeCalculation($wound, 0);
        Bleeding::createIt($wound);
    }
}
