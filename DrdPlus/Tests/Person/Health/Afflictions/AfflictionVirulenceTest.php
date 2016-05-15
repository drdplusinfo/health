<?php
namespace DrdPlus\Tests\Person\Health\Afflictions;

use DrdPlus\Person\Health\Afflictions\AfflictionVirulence;

class AfflictionVirulenceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function I_can_get_every_virulence()
    {
        $roundVirulence = AfflictionVirulence::getRoundVirulence();
        self::assertInstanceOf(AfflictionVirulence::class, $roundVirulence);
        self::assertSame('round', $roundVirulence->getValue());

        $minuteVirulence = AfflictionVirulence::getMinuteVirulence();
        self::assertInstanceOf(AfflictionVirulence::class, $minuteVirulence);
        self::assertSame('minute', $minuteVirulence->getValue());

        $hourVirulence = AfflictionVirulence::getHourVirulence();
        self::assertInstanceOf(AfflictionVirulence::class, $hourVirulence);
        self::assertSame('hour', $hourVirulence->getValue());

        $dayVirulence = AfflictionVirulence::getDayVirulence();
        self::assertInstanceOf(AfflictionVirulence::class, $dayVirulence);
        self::assertSame('day', $dayVirulence->getValue());
    }

    /**
     * @test
     * @expectedException \DrdPlus\Person\Health\Afflictions\Exceptions\UnknownVirulencePeriod
     * @expectedExceptionMessageRegExp ~life~
     */
    public function I_can_not_create_custom_virulence()
    {
        AfflictionVirulence::getEnum('life');
    }
}
