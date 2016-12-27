<?php
namespace DrdPlus\Tests\Health\Afflictions;

use DrdPlus\Health\Health;
use Granam\Tests\Exceptions\Tools\AbstractExceptionsHierarchyTest;

class ExceptionsHierarchyTest extends AbstractExceptionsHierarchyTest
{
    protected function getTestedNamespace()
    {
        return str_replace('\Tests', '', __NAMESPACE__);
    }

    protected function getRootNamespace()
    {
        return (new \ReflectionClass(Health::class))->getNamespaceName();
    }

}