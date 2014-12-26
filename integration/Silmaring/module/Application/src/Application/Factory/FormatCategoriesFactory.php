<?php
namespace Application\Factory;

use Application\Plugin\FormatCategories;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FormatCategoriesFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceManager = $serviceLocator->getServiceLocator();
        return new FormatCategories($serviceManager);
    }
}