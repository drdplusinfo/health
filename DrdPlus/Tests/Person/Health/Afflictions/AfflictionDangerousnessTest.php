<?php
namespace DrdPlus\Tests\Person\Health\Afflictions;

use Doctrineum\Integer\IntegerEnum;
use DrdPlus\Person\Health\Afflictions\AfflictionDangerousness;

class AfflictionDangerousnessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function I_can_use_it()
    {
        for ($dangerousness = -999; $dangerousness < 1000; $dangerousness += 123) {
            $afflictionDangerousness = AfflictionDangerousness::getIt($dangerousness);
            self::assertInstanceOf(AfflictionDangerousness::class, $afflictionDangerousness);
            self::assertInstanceOf(IntegerEnum::class, $afflictionDangerousness);
            self::assertSame($dangerousness, $afflictionDangerousness->getValue());
        }
    }
}
