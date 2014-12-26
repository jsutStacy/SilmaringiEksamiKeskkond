<?php

namespace Application\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Assertion\ManageSchool;

class ManageSchoolAssertionFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $manageSchool = new ManageSchool();
        $manageSchool->setServiceLocator($serviceLocator);
        return $manageSchool;
    }
}