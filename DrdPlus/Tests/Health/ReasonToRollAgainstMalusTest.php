<?php
namespace DrdPlus\Tests\Health;

use DrdPlus\Health\ReasonToRollAgainstMalus;

class ReasonToRollAgainstMalusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function I_can_use_wound_reason()
    {
        $woundReason = ReasonToRollAgainstMalus::getWoundReason();
        self::assertInstanceOf(ReasonToRollAgainstMalus::class, $woundReason);
        self::assertTrue($woundReason->becauseOfWound());
        self::assertFalse($woundReason->becauseOfHeal());
        self::assertSame('wound', $woundReason->getValue());
        self::assertSame('wound', ReasonToRollAgainstMalus::WOUND);
        self::assertSame(ReasonToRollAgainstMalus::getIt('wound'), $woundReason);
    }

    public function I_can_use_heal_reason()
    {
        $healReason = ReasonToRollAgainstMalus::getHealReason();
        self::assertInstanceOf(ReasonToRollAgainstMalus::class, $healReason);
        self::assertTrue($healReason->becauseOfHeal());
        self::assertFalse($healReason->becauseOfWound());
        self::assertSame('heal', $healReason->getValue());
        self::assertSame('heal', ReasonToRollAgainstMalus::HEAL);
        self::assertSame(ReasonToRollAgainstMalus::getIt('heal'), $healReason);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Health\Exceptions\UnknownReasonToRollAgainstMalus
     * @expectedExceptionMessageRegExp ~hypochondriac~
     */
    public function I_can_not_create_unknown_reason()
    {
        ReasonToRollAgainstMalus::getEnum('hypochondriac');
    }
}
