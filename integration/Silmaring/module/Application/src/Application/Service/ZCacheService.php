<?php
namespace Application\Service;

use Zend\Cache\StorageFactory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ZCacheService implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return StorageFactory::factory(array(
            'adapter' => array(
                'name' => 'filesystem',
                'options' => array(
                    'cache_dir' => __DIR__ . '/../../../../../data/cache/categories',
                    'ttl' => 604800000 //1 week
                ),
            ),
            'plugins' => array(
                'exception_handler' => array('throw_exceptions' => false),
                'serializer'
            )
        ));
    }
}