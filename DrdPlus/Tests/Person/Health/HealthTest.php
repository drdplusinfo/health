<?php
namespace DrdPlus\Tests\Person\Health;

class HealthTest extends \PHPUnit_Framework_TestCase
{
    // TODO getUnhealedOrdinaryWoundsValue should be same value as GridOfWounds()->getSumOfWounds() - TreatmentBoundary()->getValue()

    // TODO healSeriousAndOrdinaryWoundsUpTo after it the TreatmentBoundary has to be at least zero

    protected function setUp()
    {
        self::markTestSkipped('The most complex entity will be tested at last');
    }
}
