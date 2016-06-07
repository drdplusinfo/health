<?php
namespace DrdPlus\Tests\Health\Afflictions;

use DrdPlus\Health\Afflictions\AfflictionName;

class AfflictionNameTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function I_can_create_any_name()
    {
        $afflictionName = AfflictionName::getIt('foo');
        self::assertInstanceOf(AfflictionName::class, $afflictionName);
        self::assertSame('foo', $afflictionName->getValue());
    }

    /**
     * @test
     * @expectedException \DrdPlus\Health\Afflictions\Exceptions\AfflictionNameCanNotBeEmpty
     */
    public function I_can_not_create_empty_name()
    {
        AfflictionName::getIt('');
    }
}
