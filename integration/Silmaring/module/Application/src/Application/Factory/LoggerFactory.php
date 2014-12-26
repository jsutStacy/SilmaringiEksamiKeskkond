<?php

namespace Application\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;

class LoggerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $logger = new Logger;
        $writer = new Stream('./data/log/'.date('Y-m-d').'-error.log');

        $logger->addWriter($writer);

        return $logger;
    }
}