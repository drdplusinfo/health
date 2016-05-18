<?php
namespace DrdPlus\Tests\Person\Health\Afflictions\SpecificAfflictions;

use DrdPlus\Codes\PropertyCodes;
use DrdPlus\Person\Health\Afflictions\AfflictionDangerousness;
use DrdPlus\Person\Health\Afflictions\AfflictionDomain;
use DrdPlus\Person\Health\Afflictions\AfflictionName;
use DrdPlus\Person\Health\Afflictions\AfflictionProperty;
use DrdPlus\Person\Health\Afflictions\AfflictionSize;
use DrdPlus\Person\Health\Afflictions\AfflictionSource;
use DrdPlus\Person\Health\Afflictions\AfflictionVirulence;
use DrdPlus\Person\Health\Afflictions\Effects\ColdEffect;
use DrdPlus\Person\Health\Afflictions\ElementalPertinence\WaterPertinence;
use DrdPlus\Person\Health\Afflictions\SpecificAfflictions\Cold;
use DrdPlus\Tests\Person\Health\Afflictions\AfflictionByWoundTest;

class ColdTest extends AfflictionByWoundTest
{
    /**
     * @test
     */
    public function I_can_use_it()
    {
        $cold = Cold::createIt($wound = $this->createWound());

        self::assertNull($cold->getId());
        self::assertSame($wound, $cold->getWound());

        self::assertInstanceOf(AfflictionDomain::class, $cold->getDomain());
        self::assertSame(AfflictionDomain::PHYSICAL, $cold->getDomain()->getValue());

        self::assertInstanceOf(AfflictionVirulence::class, $cold->getVirulence());
        self::assertSame(AfflictionVirulence::DAY, $cold->getVirulence()->getValue());

        self::assertInstanceOf(AfflictionSource::class, $cold->getSource());
        self::assertSame(AfflictionSource::ACTIVE, $cold->getSource()->getValue());

        self::assertInstanceOf(AfflictionProperty::class, $cold->getProperty());
        self::assertSame(PropertyCodes::TOUGHNESS, $cold->getProperty()->getValue());

        self::assertInstanceOf(AfflictionDangerousness::class, $cold->getDangerousness());
        self::assertSame(7, $cold->getDangerousness()->getValue());

        self::assertInstanceOf(AfflictionSize::class, $cold->getSize());
        self::assertSame(4, $cold->getSize()->getValue());

        self::assertInstanceOf(WaterPertinence::class, $cold->getElementalPertinence());
        self::assertTrue($cold->getElementalPertinence()->isPlus());

        self::assertInstanceOf(ColdEffect::class, $cold->getEffect());

        self::assertInstanceOf(\DateInterval::class, $cold->getOutbreakPeriod());
        self::assertSame('0y0m1d0h0i0s', $cold->getOutbreakPeriod()->format('%yy%mm%dd%hh%ii%ss'));

        self::assertInstanceOf(AfflictionName::class, $cold->getName());
        self::assertSame('cold', $cold->getName()->getValue());
    }

}
