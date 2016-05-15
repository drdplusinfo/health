<?php
namespace DrdPlus\Tests\Person\Health\Afflictions;

use DrdPlus\Person\Health\Afflictions\AfflictionName;

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
     * @expectedException \DrdPlus\Person\Health\Afflictions\Exceptions\AfflictionNameCanNotBeEmpty
     */
    public function I_can_not_create_empty_name()
    {
        AfflictionName::getIt('');
    }
}
