<?php
namespace DrdPlus\Health;

use Doctrineum\String\StringEnum;

abstract class WoundOrigin extends StringEnum
{
    abstract public function isSeriousWoundOrigin(): bool;

    abstract public function isOrdinaryWoundOrigin(): bool;

}