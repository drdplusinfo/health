<?php
namespace DrdPlus\Tests\Health;

use Doctrineum\Integer\IntegerEnum;
use DrdPlus\Health\TreatmentBoundary;

class TreatmentBoundaryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function I_can_create_treatment_boundary()
    {
        $treatmentBoundary = TreatmentBoundary::getIt($value = 0);
        self::assertInstanceOf(TreatmentBoundary::class, $treatmentBoundary);
        self::assertInstanceOf(IntegerEnum::class, $treatmentBoundary);
        self::assertSame($value, $treatmentBoundary->getValue());
    }

    /**
     * @test
     * @expectedException \DrdPlus\Health\Exceptions\TreatmentBoundaryCanNotBeNegative
     * @expectedExceptionMessageRegExp ~Why you ask me?~
     */
    public function I_am_stopped_by_specific_exception_on_invalid_value()
    {
        TreatmentBoundary::getIt('Why you ask me?');
    }

    /**
     * @test
     * @expectedException \DrdPlus\Health\Exceptions\TreatmentBoundaryCanNotBeNegative
     * @expectedExceptionMessageRegExp ~-1~
     */
    public function I_can_not_use_negative_value()
    {
        TreatmentBoundary::getIt(-1);
    }
}
