<?php

namespace Application\Navigation;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Navigation\Service\DefaultNavigationFactory;

class SingleSchoolNavigationFactory extends DefaultNavigationFactory
{
    /**
     * Entity manager
     * @var $em
     */
    protected $em;

    /**
     * Service locator
     * @var $sm
     */
    protected $sm;

    /**
     * @var array
     */
    protected $allowedActions = array(
        'index',
        'manage',
        'edit-teacher',
        'add-teacher',
        'add-student',
        'edit-student',
        'manage-teachers',
        'manage-students'
    );

    /**
     * @var array
     */
    protected $allowedControllers = array(
        'AmaSchools\Controller\Index',
        'AmaUsers\Controller\School'
    );

    protected function getName()
    {
        return 'single_shool_navigation';
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @throws Exception\InvalidArgumentException
     * @return array
     */
    protected function getPages(ServiceLocatorInterface $serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);

        if (null === $this->pages) {
            $matchedRoute = $this->getServiceLocator()->get('Application')->getMvcEvent()->getRouteMatch();
            if(!isset($matchedRoute)) return $this->pages;

            $auth = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
            $user = $auth->getIdentity();
            $userSchools = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->findSchoolByUser($user);
            $row = $userSchools[0];
            if ( empty($row)) return $this->pages;
            $translator = $this->getServiceLocator()->get('translator');

            $id = $matchedRoute->getParam('id');
            $action = $matchedRoute->getParam('action');
            $controller = $matchedRoute->getParam('controller');

            $configuration['navigation'][$this->getName()] = array();

            /*$pages[] =  array(
                'label' => $translator->translate("Manage teachers"),
                'route' => 'mySchools',
                'action' => 'manage',
                'params' => array('whom' => 'teachers', 'id' => $row->getId()),
            );

            $pages[] = array(
                'label' => $translator->translate("Manage students"),
                'route' => 'mySchools',
                'action' => 'manage',
                'params' => array('whom' => 'students', 'id' => $row->getId()),
            );*/



            $pages[] =  array(
                'label' => $translator->translate("Manage teachers"),
                'route' => 'mySchools',
                'action' => 'manage-teachers',
                'params' => array('id' => $id),
                'active' => (in_array($action, $this->allowedActions) && in_array($controller, $this->allowedControllers)  && ($action == 'manage-teachers' || $action == 'add-teacher' || $action == 'edit-teacher' )),
                'pages' => array(
                    array(
                        'label' => $translator->translate("Add teacher"),
                        'route' => 'mySchools',
                        'action' => 'add-teacher',
                        'params' => array('id' => $id),
                        'active' => (in_array($action, $this->allowedActions) && in_array($controller, $this->allowedControllers) && $action == 'add-teacher')
                    ),

                )
            );

            $pages[] = array(
                'label' => $translator->translate("Manage students"),
                'route' => 'mySchools',
                'action' => 'manage-students',
                'params' => array('id' => $id),
                'active' => (in_array($action, $this->allowedActions) && in_array($controller, $this->allowedControllers) && ($action == 'manage-students'  || $action == 'add-student' || $action =='edit-student') ),
                'pages' => array(
                    array(
                        'label' => $translator->translate("Add student"),
                        'route' => 'mySchools',
                        'action' => 'add-student',
                        'params' => array('id' => $id),
                        'active' => (in_array($action, $this->allowedActions) && in_array($controller, $this->allowedControllers) && $action == 'add-student')
                    )
                )
            );

            $configuration['navigation'][$this->getName()][$translator->translate("School settings")] = array(
                'label' => $translator->translate("School settings"),
                'route' => 'mySchools',
                'action' => 'manage',
                'params' => array('id' => $row->getId()),
                'is_title' => true,
                'active' => (in_array($action, $this->allowedActions) && in_array($controller, $this->allowedControllers)),
                'pages' => $pages
            );

            if (!isset($configuration['navigation'])) {
                throw new Exception\InvalidArgumentException('Could not find navigation configuration key');
            }
            if (!isset($configuration['navigation'][$this->getName()])) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Failed to find a navigation container by the name "%s"',
                    $this->getName()
                ));
            }

            $application = $this->getServiceLocator()->get('Application');
            $routeMatch  = $application->getMvcEvent()->getRouteMatch();
            $router      = $application->getMvcEvent()->getRouter();
            $pages       = $this->getPagesFromConfig($configuration['navigation'][$this->getName()]);

            $this->pages = $this->injectComponents($pages, $routeMatch, $router);
        }

        return $this->pages;
    }

    /**
     * @return mixed
     */
    public function getEntityManager()
    {
        if (!$this->em) {
            $this->em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        }
        return $this->em;
    }

    /**
     * @param $serviceLocator
     */
    public function setServiceLocator($serviceLocator)
    {
        $this->sm = $serviceLocator;
    }

    /**
     * @return mixed
     */
    public function getServiceLocator()
    {
        return $this->sm;
    }

}