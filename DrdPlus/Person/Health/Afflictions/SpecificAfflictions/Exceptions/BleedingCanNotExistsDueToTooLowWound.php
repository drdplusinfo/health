<?php
namespace DrdPlus\Person\Health\Afflictions\SpecificAfflictions\Exceptions;

use Granam\Tests\Exceptions\Tools\DummyExceptionsHierarchy\RuntimeExceptionAsLogicException\Logic;

class BleedingCanNotExistsDueToTooLowWound extends \LogicException implements Logic
{

}