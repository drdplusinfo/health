<?php
namespace DrdPlus\Tests\Person\Health;

use DrdPlus\Codes\WoundsOriginCodes;
use Granam\Tests\Tools\TestWithMockery;

abstract class WoundOriginTest extends TestWithMockery
{
    /**
     * @return array|\string[]
     */
    protected function getSeriousWoundOriginCodes()
    {
        return WoundsOriginCodes::getOriginWithTypeCodes();
    }
}