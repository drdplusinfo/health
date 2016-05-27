<?php
namespace DrdPlus\Tests\Person\Health\Afflictions\SpecificAfflictions;

use DrdPlus\Person\Health\Afflictions\Effects\PainEffect;
use DrdPlus\Person\Health\Afflictions\SpecificAfflictions\Pain;
use DrdPlus\Tests\Person\Health\Afflictions\AfflictionByWoundTest;
use DrdPlus\Codes\PropertyCodes;
use DrdPlus\Person\Health\Afflictions\AfflictionDangerousness;
use DrdPlus\Person\Health\Afflictions\AfflictionDomain;
use DrdPlus\Person\Health\Afflictions\AfflictionName;
use DrdPlus\Person\Health\Afflictions\AfflictionProperty;
use DrdPlus\Person\Health\Afflictions\AfflictionSource;

class PainTest extends AfflictionByWoundTest
{
    /**
     * @test
     */
    public function I_can_use_it()
    {
        $wound = $this->createWound();
        $crackedBones = Pain::createIt(
            $wound,
            $virulence = $this->createAfflictionVirulence(),
            $size = $this->createAfflictionSize($painValue = 123),
            $elementalPertinence = $this->createElementalPertinence()
        );

        self::assertNull($crackedBones->getId());
        self::assertSame($wound, $crackedBones->getSeriousWound());

        self::assertInstanceOf(AfflictionDomain::class, $crackedBones->getDomain());
        self::assertSame(AfflictionDomain::PHYSICAL, $crackedBones->getDomain()->getValue());

        self::assertSame($virulence, $crackedBones->getVirulence());

        self::assertInstanceOf(AfflictionSource::class, $crackedBones->getSource());
        self::assertSame(AfflictionSource::EXTERNAL, $crackedBones->getSource()->getValue());

        self::assertInstanceOf(AfflictionProperty::class, $crackedBones->getProperty());
        self::assertSame(PropertyCodes::WILL, $crackedBones->getProperty()->getValue());

        self::assertInstanceOf(AfflictionDangerousness::class, $crackedBones->getDangerousness());
        self::assertSame($painValue + 10, $crackedBones->getDangerousness()->getValue());

        self::assertSame($size, $crackedBones->getSize());

        self::assertSame($elementalPertinence, $crackedBones->getElementalPertinence());

        self::assertInstanceOf(PainEffect::class, $crackedBones->getEffect());

        self::assertInstanceOf(\DateInterval::class, $crackedBones->getOutbreakPeriod());
        self::assertSame('0y0m0d0h0i0s', $crackedBones->getOutbreakPeriod()->format('%yy%mm%dd%hh%ii%ss'));

        self::assertInstanceOf(AfflictionName::class, $crackedBones->getName());
        self::assertSame('pain', $crackedBones->getName()->getValue());
    }

}
