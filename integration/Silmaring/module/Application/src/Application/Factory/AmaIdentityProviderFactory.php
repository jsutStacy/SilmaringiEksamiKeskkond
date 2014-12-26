<?php

namespace Application\Factory;

use Application\Service\AmaIdentityProviderService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AmaIdentityProviderFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $em = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $provider = new AmaIdentityProviderService($em);
        $provider->setUserService($serviceLocator->get('zfcuser_user_service'));
        return $provider;
    }
}