<?php

namespace Application\Helper;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\AbstractHelper;

class Params extends AbstractHelper implements serviceLocatorAwareInterface
{
    protected  $serviceLocator;

    protected $app;


    public function __invoke(){
        $sm = $this->getServiceLocator()->getServiceLocator();
        $this->app = $sm->get('Application');
        return $this;
    }

    public function fromQuery($param = null) {
        $request = $this->app->getRequest();
        if(!is_object($request)) return null;
        return $request->getQuery($param);
    }

    public function fromRoute($param = null, $default = null)
    {
        $event = $this->app->getMvcEvent();
        if(!is_object($event->getRouteMatch())) return null;
        return $event->getRouteMatch()->getParam($param, $default);
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }
}