<?php
namespace DrdPlus\Tests\Health\Afflictions;

use DrdPlus\Health\Health;
use Granam\Tests\ExceptionsHierarchy\Exceptions\AbstractExceptionsHierarchyTest;

class HealthAfflictionsExceptionsHierarchyTest extends AbstractExceptionsHierarchyTest
{
    /**
     * @return string
     */
    protected function getTestedNamespace()
    {
        return str_replace('\Tests', '', __NAMESPACE__);
    }

    /**
     * @return string
     */
    protected function getRootNamespace()
    {
        return (new \ReflectionClass(Health::class))->getNamespaceName();
    }

}