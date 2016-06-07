<?php
namespace DrdPlus\Tests\Health\Afflictions\SpecificAfflictions;

use DrdPlus\Health\Afflictions\Effects\CrackedBonesEffect;
use DrdPlus\Health\Afflictions\ElementalPertinence\EarthPertinence;
use DrdPlus\Health\Afflictions\SpecificAfflictions\CrackedBones;
use DrdPlus\Health\GridOfWounds;
use DrdPlus\Health\Wound;
use DrdPlus\Tests\Health\Afflictions\AfflictionByWoundTest;
use DrdPlus\Codes\PropertyCodes;
use DrdPlus\Health\Afflictions\AfflictionDangerousness;
use DrdPlus\Health\Afflictions\AfflictionDomain;
use DrdPlus\Health\Afflictions\AfflictionName;
use DrdPlus\Health\Afflictions\AfflictionProperty;
use DrdPlus\Health\Afflictions\AfflictionSize;
use DrdPlus\Health\Afflictions\AfflictionSource;
use DrdPlus\Health\Afflictions\AfflictionVirulence;

class CrackedBonesTest extends AfflictionByWoundTest
{
    /**
     * @test
     */
    public function I_can_use_it()
    {
        $wound = $this->createWound();
        $this->addSizeCalculation($wound, $filledHalfOfRows = 3);
        $crackedBones = CrackedBones::createIt($wound);

        self::assertNull($crackedBones->getId());
        self::assertSame($wound, $crackedBones->getSeriousWound());

        self::assertInstanceOf(AfflictionDomain::class, $crackedBones->getDomain());
        self::assertSame(AfflictionDomain::PHYSICAL, $crackedBones->getDomain()->getValue());

        self::assertInstanceOf(AfflictionVirulence::class, $crackedBones->getVirulence());
        self::assertSame(AfflictionVirulence::DAY, $crackedBones->getVirulence()->getValue());

        self::assertInstanceOf(AfflictionSource::class, $crackedBones->getSource());
        self::assertSame(AfflictionSource::PASSIVE, $crackedBones->getSource()->getValue());

        self::assertInstanceOf(AfflictionProperty::class, $crackedBones->getProperty());
        self::assertSame(PropertyCodes::TOUGHNESS, $crackedBones->getProperty()->getValue());

        self::assertInstanceOf(AfflictionDangerousness::class, $crackedBones->getDangerousness());
        self::assertSame(15, $crackedBones->getDangerousness()->getValue());

        self::assertInstanceOf(AfflictionSize::class, $crackedBones->getSize());
        self::assertSame($filledHalfOfRows * 2, $crackedBones->getSize()->getValue());

        self::assertInstanceOf(EarthPertinence::class, $crackedBones->getElementalPertinence());
        self::assertTrue($crackedBones->getElementalPertinence()->isMinus());

        self::assertInstanceOf(CrackedBonesEffect::class, $crackedBones->getEffect());

        self::assertInstanceOf(\DateInterval::class, $crackedBones->getOutbreakPeriod());
        self::assertSame('0y0m0d0h0i0s', $crackedBones->getOutbreakPeriod()->format('%yy%mm%dd%hh%ii%ss'));

        self::assertInstanceOf(AfflictionName::class, $crackedBones->getName());
        self::assertSame('cracked_bones', $crackedBones->getName()->getValue());
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

}
