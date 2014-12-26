<?php

namespace Eksamikool\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Eksamikool\Service\EmailService;

class EmailServiceFactory implements FactoryInterface {

	public function createService(ServiceLocatorInterface $serviceLocator) {
		return new EmailService();
	}
}