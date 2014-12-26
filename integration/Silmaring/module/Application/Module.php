<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Session\Container;
use Zend\Validator\AbstractValidator;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $application = $e->getApplication();
        $eventManager        = $application->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        $sm = $application->getServiceManager();
        $sharedManager = $application->getEventManager()->getSharedManager();

        $translator = $sm->get('translator');
        AbstractValidator::setDefaultTranslator($translator);

        $sharedManager->attach('Zend\Mvc\Application', 'dispatch.error',
            function ($e) use ($sm) {
                if ($e->getParam('exception')) {
                    $sm->get('Zend\Log\Logger')->crit($e->getParam('exception'));
                }
            }
        );

        /*$sharedManager->attach('Zend\Mvc\Application', MvcEvent::EVENT_DISPATCH,
            array($this, 'checkLogin'), 100);*/

        $this->bootstrapSession($e);

    }

    public function checkLogin(MvcEvent $e)
    {
        $sm = $e->getApplication()->getServiceManager();
        $auth = $sm->get('doctrine.authenticationservice.orm_default');
        if ($auth->hasIdentity()) return;

        if ($e->getRouteMatch()->getMatchedRouteName() == 'home') { return; }

        $url = $e->getRouter()->assemble(array('controller' => 'Application\Controller\Index'), array('name' => 'home'));
        $response = $e->getResponse();
        $response->setHeaders($response->getHeaders()->addHeaderLine('Location', $url));
        $response->setStatusCode(302);
        $response->sendHeaders();
        return;
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function bootstrapSession($e)
    {
        $session = $e->getApplication()
            ->getServiceManager()
            ->get('Zend\Session\SessionManager');
        $session->start();

        $container = new Container('initialized');
        if (!isset($container->init)) {
            $session->regenerateId(true);
            $container->init = 1;
        }
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Zend\Authentication\AuthenticationService' => function ($serviceManager) {
                        // If you are using DoctrineORMModule:
                        return $serviceManager->get('doctrine.authenticationservice.orm_default');
                    },
            )
        );
    }
}
