<?php
namespace DrdPlus\Tests\Health;

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