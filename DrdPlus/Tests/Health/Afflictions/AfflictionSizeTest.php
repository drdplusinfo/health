<?php
namespace DrdPlus\Tests\Health;

use DrdPlus\Health\Afflictions\AfflictionSize;
use Granam\Integer\IntegerInterface;

class AfflictionSizeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function I_can_use_it_as_an_integer()
    {
        $afflictionSize = AfflictionSize::getIt(123);
        self::assertInstanceOf(IntegerInterface::class, $afflictionSize);
        self::assertSame(123, $afflictionSize->getValue());
    }

    /**
     * @test
     * @expectedException \Doctrineum\Integer\Exceptions\UnexpectedValueToConvert
     * @expectedExceptionMessageRegExp ~Broken heart by fixed dart~
     */
    public function I_am_stopped_by_specific_exception_on_invalid_value()
    {
        new AfflictionSize('Broken heart by fixed dart');
    }

    /**
     * @test
     * @expectedException \DrdPlus\Health\Afflictions\Exceptions\AfflictionSizeCanNotBeNegative
     * @expectedExceptionMessageRegExp ~-1~
     */
    public function I_can_not_use_negative_value()
    {
        new AfflictionSize(-1);
    }
}
