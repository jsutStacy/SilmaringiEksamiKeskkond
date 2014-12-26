<?php

namespace AmaUsers\Controller;

use Zend\Paginator\Adapter\Null;
use Zend\Paginator\Paginator;
use AmaUsers\Entity\CustomerInvitation;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class DashboardController extends AbstractActionController
{

    /**
     * Main config
     * @var $config
     */
    protected $config;

    /**
     * Entity Manager
     * @var $em
     */
    protected $em;

    protected $limit = 5;


    public function indexAction()
    {
        $this->getConfig();

        $user = $this->identity();

        $template = 'ama-users/dashboard/index';
        if ( $user->hasRole('k_student') || $user->hasRole('v_student') ) {
            $template = 'ama-users/dashboard/student-index';
        }

        $allCategories = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->findAllCategoriesScalar();
        if ($user->hasRole('k_student')) {
            $materialsArray = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->findUserClassesFiles($user);
        }
        else {
            $materialsArray = $this->getEntityManager()->getRepository('AmaMaterials\Entity\LessonPlanFile')->findLessonPlanCategoriesAndFiles($user);
        }

        $materials = array();
        foreach($materialsArray as $material) {
            $materials[$material['category_id']][] = $material;
        }
        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
                'user'  => $user,
                'materials' => $materials,
                'allCategories' => $allCategories,
                'isParentCategory' => true,
                'em' => $this->getEntityManager()
            ))
            ->setTemplate($template);

        return $viewModel;
    }

    public function categoryAction()
    {

        if ($this->params('id')==0) {
            return $this->redirect()->toRoute('error');
        }

        $user = $this->identity();
        $template = 'ama-users/dashboard/index';
        if ( $user->hasRole('k_student') || $user->hasRole('v_student') ) {
            $template = 'ama-users/dashboard/student-index';
        }

        $category = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->find($this->params('id'));
        if (!$category) {
            return $this->redirect()->toRoute('error');
        }

        $allCategories = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->findAllCategoriesScalar();
        $categoriesFormated = $this->formatCategories()->doFormat($allCategories, $category->getId());
        $categories = $this->formatCategories()->getChildren($categoriesFormated, $category->getId());

        $isParent = $this->isParentCategory($category);

        $materialsArray = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->findUserClassesFilesWithCategory($user, $categories, $category->getId(), array());

        $materials = array();
        foreach($materialsArray as $material) {
            $materials[$material['category_id']][] = $material;
        }

        $this->layout()->setVariable('category', $category);

        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            'user'  => $user,
            'materials' => $materials,
            'allCategories' => $allCategories,
            'category' => $category,
            'isParentCategory' => $isParent,
        ))
         ->setTemplate($template);

        return $viewModel;
    }

    public function checkAlertsAction()
    {
        $request = $this->getRequest();
        if (!$request->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        $user = $this->identity();
        $alerts = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->getAlerts($user);
        $alertsCount = count($alerts);

        if ($alertsCount>0){
            $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaAlerts' . $user->getId());
        }

        $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->updateOnlineTime($user);

        $htmlViewPart = new ViewModel();
        $htmlViewPart->setVariables(array(
            'user'  => $user,
            'alerts' => $alerts,
            'alertsCount' => $alertsCount
        ))
         ->setTerminal(true)
         ->setTemplate('ama-users/dashboard/alerts');

        $htmlOutput = $this->getServiceLocator()
            ->get('viewrenderer')
            ->render($htmlViewPart);

        return new JsonModel(array(
            'success' => true,
            'count' => $alertsCount,
            'html' => $htmlOutput
        ));
    }


    public function markAlertsReadAction()
    {
        $request = $this->getRequest();
        if (!$request->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }
        $user = $this->identity();
        $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->markAlertsRead($user);

        return new JsonModel(array(
            'success' => true
        ));
    }


    public function searchAction()
    {
        $user = $this->identity();

        $template = 'ama-users/dashboard/search';
        if ( $user->hasRole('k_student') || $user->hasRole('v_student') ) {
            $template = 'ama-users/dashboard/search-student';
        }
        $term = $this->getRequest()->getQuery('term');
        $termLength = strlen($term);
        if(!empty($term) && $termLength>1) {
            if ($user->hasRole('k_student')) {
                $materialsArray = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->findUserClassesFiles($user, 15000, $term);
            }
            else {
                $materialsArray = $this->getEntityManager()->getRepository('AmaMaterials\Entity\LessonPlanFile')->findLessonPlanCategoriesAndFiles($user, 15000, $term);
            }
        }
        else {
            $materialsArray = array();
        }


        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            'user'  => $user,
            'materials' => $materialsArray,
            'materialsCount' => count($materialsArray),
            'isParentCategory' => true,
            'termLength' => $termLength,
            'em' => $this->getEntityManager()
        ))
        ->setTemplate($template);

        return $viewModel;
    }

    public function isParentCategory($category)
    {
        $childCategory = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->findOneBy(array('parent' => $category));
        if ($childCategory) return true;

        return false;
    }

    public function getConfig()
    {
        if (!$this->config) {
            return $this->config = $this->getServiceLocator()->get('config');
        }
        return $this->config;
    }

    public function getEntityManager()
    {
        if (!$this->em) {
            $this->em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        }
        return $this->em;
    }

    /**
     * Format error messages
     * @param $messages
     * @return string
     */
    public function formatMessage($messages = '')
    {
        $return = '';
        if (is_array($messages)) {
            foreach ($messages as $message) {
                if (is_array($message)) {
                    foreach ($message as $m) {
                        $return .= $m . '<br>';
                    }
                } else {
                    $return .= $message . '<br>';
                }
            }
        } else {
            $return = $messages;
        }
        return $return;
    }
}