<?php
namespace DrdPlus\Health;

use Doctrineum\String\StringEnum;

abstract class WoundOrigin extends StringEnum
{
    /**
     * @return bool
     */
    abstract public function isSeriousWoundOrigin();

    /**
     * @return bool
     */
    abstract public function isOrdinaryWoundOrigin();

}