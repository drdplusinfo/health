<?php
namespace DrdPlus\Tests\Person\Health\Afflictions\Effects;

use DrdPlus\Person\Health\Afflictions\Effects\AfflictionEffect;
use Granam\String\StringTools;
use Granam\Tests\Tools\TestWithMockery;

abstract class AfflictionEffectTest extends TestWithMockery
{
    /**
     * @test
     */
    public function I_can_use_it()
    {
        $sutClass = $this->getSutClass();
        /** @var AfflictionEffect $effect */
        $effect = $sutClass::getIt();
        self::assertInstanceOf($sutClass, $effect);
        self::assertSame(
            $effect,
            $sameEffect = $sutClass::getEnum($this->getEffectCode()),
            "Expected {$effect} to be the very same instance as {$sameEffect}"
        );
        self::assertInstanceOf($sutClass, $effect);
        self::assertSame($this->getEffectCode(), $effect->getValue());
    }

    private function getSutClass()
    {
        return preg_replace('~[\\\]Tests([\\\].+)Test$~', '$1', static::class);
    }

    private function getEffectCode()
    {
        return StringTools::camelCaseToSnakeCasedBasename($this->getSutClass());
    }

    /**
     * @test
     */
    abstract public function I_can_find_out_if_apply_even_on_success_against_trap();
}