<?php

namespace Application\Navigation;

use AmaCategories\Entity\Category;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Navigation\Service\DefaultNavigationFactory;

class CategoryNavigationFactory extends DefaultNavigationFactory
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
    protected $allowedActions = array('category');

    /**
     * @var array
     */
    protected $allowedControllers = array(
        'AmaMaterials\Controller\Index',
        'AmaMaterials\Controller\LessonPlan',
        'AmaUsers\Controller\Dashboard',
        'AmaTests\Controller\Index',
        'AmaWorksheets\Controller\Index'
    );

    protected $userCategories = null;

    protected function getName()
    {
        return 'category_navigation';
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
            $categories = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->findAllTopCategories();
            $matchedRoute = $this->getServiceLocator()->get('Application')->getMvcEvent()->getRouteMatch();


            if(!isset($matchedRoute)) return $this->pages;
            if(!$categories) return $this->pages;

            $auth = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
            $user = $auth->getIdentity();

            if($user) {
                $this->userCategories = $this->getEntityManager()->getRepository('AmaUsers\Entity\UserCategory')->getUserCategories($user);
            }

            $id = $matchedRoute->getParam('id');
            $action = $matchedRoute->getParam('action');
            $controller = $matchedRoute->getParam('controller');

            $route = 'materials';
            $raction = 'category';
            if($controller == 'AmaMaterials\Controller\LessonPlan') {
                $route = 'lessonPlans';
            }
            if($controller == 'AmaUsers\Controller\Dashboard') {
                $route = 'dashboard';
                if($user->hasRole('k_teacher') || $user->hasRole('school')) {
                    $route = 'lessonPlans';
                }
            }
            if($controller == 'AmaTests\Controller\Index') {
                $route = 'tests';
            }

            if($controller == 'AmaWorksheets\Controller\Index') {
                $route = 'worksheets';
            }

            if($user->hasRole('k_student') || $user->hasRole('v_student')  || $user->hasRole('v_teacher')) {
                $route = 'dashboard';
            }

            $cache = $this->getServiceLocator()->get('zcache');
            $key = 'category-'. $user->getId() . '-'  . $id . '-' . $route ;
            $success = false;
            $configuration['navigation'][$this->getName()] = array();
            $navigation = $cache->getItem($key, $success);

            if(empty($navigation)) $success = false;
            //$success = false;

           if ( !$success ) {

               $subCategories = array();
               $parents = array();
               if($id>0) {
                   $chosenCat = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->find($id);
                   $depth = -1;
                   if($chosenCat) {
                       $depth = $chosenCat->getDepth();
                   }
                   $parents = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->findCatTreeAsIds($id);
                   $reversed_parents = array_reverse($parents);
                   $subCategories = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->findAllCategoriesById($reversed_parents[0], $depth+1);
                   $subCategories = $this->fixSubCategories($subCategories, $reversed_parents[0]);
               }

               foreach ($categories as $category) {
                if($this->userCategories && !in_array($category->getId(), $this->userCategories)) continue;
                $navigation[$category->getName()] = array(
                    'label' => $category->getName(),
                    'route' => $route,
                    'action' => $raction,
                    //'active' => (in_array($action, $this->allowedActions) && in_array($controller, $this->allowedControllers)  && ($this->isParent($id, $category->getId()) || $id == $category->getId())),
                    'active' => (in_array($action, $this->allowedActions) && in_array($controller, $this->allowedControllers)  && (in_array($category->getId(), $parents) || $id == $category->getId())),
                    'params' => array('id' => $category->getId()),
                    'pages' => $this->getSubPages2($subCategories, $parents, $id, $action, $controller, $route, $raction, $category->getId())
                );
            }
              $cache->setItem($key, $navigation);
           }
            $configuration['navigation'][$this->getName()] = $navigation;

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

    protected function getSubPages($parent, $id, $action, $controller, $route, $raction)
    {
        $pages = array();
        $categories = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->findSubCategories($parent);
        if (!$categories) return $pages;

        foreach($categories as $category) {
            if($this->userCategories && !in_array($category->getId(), $this->userCategories)) continue;
            $pages[] =  array(
                'label' => $category->getName(),
                'route' => $route,
                'action' => $raction,
                'active' => (in_array($action, $this->allowedActions)  && in_array($controller, $this->allowedControllers)   && ($this->isParent($id, $category->getId()) || $id == $category->getId())),
                'params' => array('id' => $category->getId()),
                'pages' => $this->getSubPages($category, $id, $action, $controller, $route, $raction)
            );
        }

        return $pages;
    }

    protected function getSubPages2($subCategories, $parents, $id, $action, $controller, $route, $raction, $current)
    {
        $origSubCategories = $subCategories;
        if(isset($subCategories[$current])) {
            $subCategories = $subCategories[$current];
        }
        else {
            return array();
        }

        $pages = array();
        if(!$subCategories) return $pages;

        foreach($subCategories as $category) {
            if($this->userCategories && !in_array($category['c_id'], $this->userCategories) && $category['c_depth']<=1) continue;
            //if($category->getId()==$id) continue;
            $pages[] =  array(
                'label' => $category['c_name'],
                'route' => $route,
                'action' => $raction,
                'active' => (in_array($action, $this->allowedActions)  && in_array($controller, $this->allowedControllers)   && (in_array($category['c_id'], $parents) || $id == $category['c_id'])),
                'params' => array('id' => $category['c_id']),
                'pages' => $this->getSubPages2($origSubCategories, $parents, $id, $action, $controller, $route, $raction, $category['c_id'])
            );
        }

        return $pages;
    }

    protected function isParent($currentCat, $categoryId)
    {
        $category = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->findCategoryById($currentCat);
        if (!$category) return false;
        $parent = $category->getParent();
        if ($parent==null) return false;
        if ($parent->getId()!=$categoryId) {
            return $this->isParent($parent->getId(), $categoryId);
        }
        else {
            return true;
        }
    }


    protected function fixSubCategories($subCategories, $currentCat)
    {
        $newSubCategories = array();
        foreach($subCategories as $category) {
                if(($category['c_id']==$currentCat && !$category['cpp_id']) || !$category['cpp_id']) continue;
                //if($category['c_name']=='[P]') continue;
                $newSubCategories[$category['cpp_id']][] = $category;
        }
        return $newSubCategories;
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