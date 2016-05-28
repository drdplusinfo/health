<?php
namespace DrdPlus\Tests\Person\Health;

use DrdPlus\Person\Health\ReasonToRollAgainstMalus;
use Granam\Tests\Tools\TestWithMockery;

class ReasonToRollAgainstMalusTest extends TestWithMockery
{
    /**
     * @test
     * @expectedException \DrdPlus\Person\Health\Exceptions\UnknownReasonToRollAgainstMalus
     * @expectedExceptionMessageRegExp ~hypochondriac~
     */
    public function I_can_not_create_unknown_reason()
    {
        ReasonToRollAgainstMalus::getEnum('hypochondriac');
    }
}
