<?php
namespace Application\Service;

use Zend\Cache\StorageFactory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class WPCacheService implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return StorageFactory::factory(array(
            'adapter' => array(
                'name' => 'filesystem',
                'options' => array(
                    'cache_dir' => __DIR__ . '/../../../../../data/cache/wp-navigation',
                    'ttl' => 3600*4 //4 hours
                ),
            ),
            'plugins' => array(
                'exception_handler' => array('throw_exceptions' => false),
                'serializer'
            )
        ));
    }
}