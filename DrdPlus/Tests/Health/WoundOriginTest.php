<?php
namespace DrdPlus\Tests\Health;

use DrdPlus\Codes\WoundsOriginCode;
use Granam\Tests\Tools\TestWithMockery;

abstract class WoundOriginTest extends TestWithMockery
{
    /**
     * @return array|\string[]
     */
    protected function getSeriousWoundOriginCodes()
    {
        return WoundsOriginCode::getWoundsOriginCodes(); // de facto all of them can be serious
    }
}