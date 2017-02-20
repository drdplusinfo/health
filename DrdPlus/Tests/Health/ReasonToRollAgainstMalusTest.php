<?php
namespace DrdPlus\Tests\Health;

use DrdPlus\Health\ReasonToRollAgainstWoundMalus;
use PHPUnit\Framework\TestCase;

class ReasonToRollAgainstMalusTest extends TestCase
{
    /**
     * @test
     */
    public function I_can_use_wound_reason()
    {
        $woundReason = ReasonToRollAgainstWoundMalus::getWoundReason();
        self::assertInstanceOf(ReasonToRollAgainstWoundMalus::class, $woundReason);
        self::assertTrue($woundReason->becauseOfWound());
        self::assertFalse($woundReason->becauseOfHeal());
        self::assertSame('wound', $woundReason->getValue());
        self::assertSame('wound', ReasonToRollAgainstWoundMalus::WOUND);
        self::assertSame(ReasonToRollAgainstWoundMalus::getIt('wound'), $woundReason);
    }

    public function I_can_use_heal_reason()
    {
        $healReason = ReasonToRollAgainstWoundMalus::getHealReason();
        self::assertInstanceOf(ReasonToRollAgainstWoundMalus::class, $healReason);
        self::assertTrue($healReason->becauseOfHeal());
        self::assertFalse($healReason->becauseOfWound());
        self::assertSame('heal', $healReason->getValue());
        self::assertSame('heal', ReasonToRollAgainstWoundMalus::HEAL);
        self::assertSame(ReasonToRollAgainstWoundMalus::getIt('heal'), $healReason);
    }

    /**
     * @test
     * @expectedException \DrdPlus\Health\Exceptions\UnknownReasonToRollAgainstMalus
     * @expectedExceptionMessageRegExp ~hypochondriac~
     */
    public function I_can_not_create_unknown_reason()
    {
        ReasonToRollAgainstWoundMalus::getEnum('hypochondriac');
    }
}