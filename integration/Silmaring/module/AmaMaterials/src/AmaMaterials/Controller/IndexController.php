<?php

namespace AmaMaterials\Controller;

use AmaMaterials\Entity\FileView;
use Application\Paginator\DoctrinePaginatorAdapter;
use Zend\Paginator\Adapter\Null;
use Zend\Paginator\Paginator;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;

class IndexController extends AbstractActionController
{
    /**
     * Limit items on page
     * @var int
     */
    protected $limit = 5;

    /**
     * Entity Manager
     * @var $em
     */
    protected $em;

    public function indexAction()
    {
        $user = $this->identity();
        $allCategories = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->findAllCategoriesScalar(5);

        $page = $this->params('page')>0?$this->params('page'):1;

        $args = array(
            'start' => ($page - 1) * $this->limit,
            'limit' => $this->limit
        );
        $materials = $this->getEntityManager()->getRepository('AmaMaterials\Entity\File')->findFiles($user, '', '', $args);
        $materialsCount = $this->getEntityManager()->getRepository('AmaMaterials\Entity\File')->findFilesCount($user, '', '', $args);

        $paginator = new Paginator(new Null($materialsCount));
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($this->limit);

        $usersClasses = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->findUsersClasses($user);
        $materialsCount = $this->getEntityManager()->getRepository('AmaMaterials\Entity\File')->countMaterialsByTypes($user);

        return new ViewModel(array(
            'materials' => $materials,
            'paginator' => $paginator,
            'allCategories' => $allCategories,
            'usersClasses' => $usersClasses,
            'em' => $this->getEntityManager(),
            'user' => $user,
            'successMessages' => $this->flashMessenger()->getMessages(),
            'materialsCount' => $materialsCount
        ));
    }

    public function pageAction()
    {
        $user = $this->identity();
        $allCategories = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->findAllCategoriesScalar(5);

        $page = $this->params('page')>0?$this->params('page'):1;

        $args = array(
            'start' => ($page - 1) * $this->limit,
            'limit' => $this->limit
        );
        $materials = $this->getEntityManager()->getRepository('AmaMaterials\Entity\File')->findFiles($user, '', '', $args);
        $materialsCount = $this->getEntityManager()->getRepository('AmaMaterials\Entity\File')->findFilesCount($user, '', '', $args);

        $paginator = new Paginator(new Null($materialsCount));
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($this->limit);

        $usersClasses = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->findUsersClasses($user);
        $materialsCount = $this->getEntityManager()->getRepository('AmaMaterials\Entity\File')->countMaterialsByTypes($user);

        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            'materials' => $materials,
            'paginator' => $paginator,
            'allCategories' => $allCategories,
            'usersClasses' => $usersClasses,
            'em' => $this->getEntityManager(),
            'user' => $user,
            'successMessages' => $this->flashMessenger()->getMessages(),
            'materialsCount' => $materialsCount
        ));
        $viewModel->setTemplate('ama-materials/index/index');

        return $viewModel;
    }

    public function categoryAction()
    {
        if ($this->params('id')==0) {
            return $this->redirect()->toRoute('error');
        }

        $category = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->find($this->params('id'));
        if (!$category) {
            return $this->redirect()->toRoute('error');
        }
        $user = $this->identity();
        $allCategories = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->findAllCategoriesScalar(5);
        $categoriesFormated = $this->formatCategories()->doFormat($allCategories, $category->getId());
        $categories = $this->formatCategories()->getChildren($categoriesFormated, $category->getId());

        $page = $this->params('page')>0?$this->params('page'):1;
        $args = array(
            'start' => ($page - 1) * $this->limit,
            'limit' => $this->limit
        );
        $materials = $this->getEntityManager()->getRepository('AmaMaterials\Entity\File')->findFiles($user, $categories, $category->getId(), $args);
        $materialsCount = $this->getEntityManager()->getRepository('AmaMaterials\Entity\File')->findFilesCount($user, $categories, $category->getId(), $args);

        $paginator = new Paginator(new Null($materialsCount));
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($this->limit);

        $usersClasses = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->findUsersClasses($user);
        $materialsCount = $this->getEntityManager()->getRepository('AmaMaterials\Entity\File')->countMaterialsByTypes($user);

        $this->layout()->setVariable('category', $category);
        return new ViewModel(array(
            'category' => $category,
            'isParentCategory' => $this->isParentCategory($category),
            'materials' => $materials,
            'paginator' => $paginator,
            'allCategories' => $allCategories,
            'usersClasses' => $usersClasses,
            'em' => $this->getEntityManager(),
            'user' => $user,
            'successMessages' => $this->flashMessenger()->getMessages(),
            'materialsCount' => $materialsCount
        ));
    }

    public function viewAction()
    {
        $request = $this->getRequest();
        if (!$request->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        if ($this->params('id')==0) {
            return $this->redirect()->toRoute('error');
        }

        if ($this->params('page')==0) {
            return $this->redirect()->toRoute('error');
        }

        if ($request->isPost()) {
            $file = $this->getEntityManager()->getRepository('AmaMaterials\Entity\LessonPlanFile')->findOneBy(array('id' => $this->params('id')));
            if (!$file) {
                return $this->redirect()->toRoute('error');
            }

            $fileClass = $this->getEntityManager()->getRepository('AmaMaterials\Entity\FileClass')->findOneBy(array('id' => $this->params('page'), 'file' => $file));
            if (!$fileClass) {
                return $this->redirect()->toRoute('error');
            }

            $user = $this->identity();
            $fileView = new FileView();
            $fileView->setFile($file);
            $fileView->setViewer($user);
            $fileView->setFileClass($fileClass);
            $this->getEntityManager()->persist($fileView);
            $this->getEntityManager()->flush();
            $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaFilesSentToClasses' . $file->getFile()->getId() . $fileClass->getSender()->getId());
        }

        return new JsonModel(array(
            'success' => true,
        ));
    }

    public function isParentCategory($category)
    {
        $childCategory = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->findOneBy(array('parent' => $category));
        if ($childCategory) return true;

        return false;
    }

    public function getEntityManager()
    {
        if (!$this->em) {
            $this->em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        }
        return $this->em;
    }
}

