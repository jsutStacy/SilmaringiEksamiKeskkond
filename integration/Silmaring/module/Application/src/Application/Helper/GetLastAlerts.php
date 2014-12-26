<?php

namespace Application\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * View Helper
 */
class GetLastAlerts extends AbstractHelper implements ServiceLocatorAwareInterface {

    protected $serviceLocator;

    protected $em;

    /**
     * Set the service locator.
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return CustomHelper
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * Get the service locator.
     *
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator() {
        return $this->serviceLocator;
    }

    public function __invoke() {

        $auth = $this->getServiceLocator()->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        if (!$auth->hasIdentity()) {
            return;
        }
        $user = $auth->getIdentity();

        $alerts = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->getLastAlerts($user);
        $alertsCount = count($alerts);

        return array(
            'alerts' => $alerts,
            'alertsCount' => $alertsCount
        );
    }

    public function getEntityManager()
    {
        if (!$this->em) {
            $this->em = $this->getServiceLocator()->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        }
        return $this->em;
    }

}