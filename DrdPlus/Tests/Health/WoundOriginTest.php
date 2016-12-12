<?php
namespace DrdPlus\Tests\Health;

use DrdPlus\Codes\Body\WoundsOriginCode;
use Granam\Tests\Tools\TestWithMockery;

abstract class WoundOriginTest extends TestWithMockery
{
    /**
     * @return array|\string[]
     */
    protected function getSeriousWoundOriginCodes()
    {
        return WoundsOriginCode::getPossibleValues(); // all of them can be serious in fact
    }
}