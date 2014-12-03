<?php

namespace EksamiKeskkond\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use EksamiKeskkond\Service\EmailService;

class EmailServiceFactory implements FactoryInterface {

	public function createService(ServiceLocatorInterface $serviceLocator) {
		return new EmailService();
	}
}