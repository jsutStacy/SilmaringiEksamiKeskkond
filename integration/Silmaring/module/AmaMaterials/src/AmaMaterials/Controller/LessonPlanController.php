<?php

namespace AmaMaterials\Controller;

use Zend\Escaper\Escaper;
use Zend\Paginator\Adapter\Null;
use Zend\Paginator\Paginator;
use AmaMaterials\Entity\LessonPlanFile;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class LessonPlanController extends AbstractActionController
{

    /**
     * Entity Manager
     * @var $em
     */
    protected $em;

    protected $limit;

    public function indexAction()
    {
        $user = $this->identity();
        $allCategories = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->findAllCategoriesScalar(5);
        $materialsArray = $this->getEntityManager()->getRepository('AmaMaterials\Entity\LessonPlanFile')->findLessonPlanCategoriesAndFiles($user);

        $materials = array();
        foreach($materialsArray as $material) {
            $materials[$material['category_id']][] = $material;
        }

        return new ViewModel(array(
            'materials' => $materials,
            'allCategories' => $allCategories,
            'em' => $this->getEntityManager(),
            'user' => $user,
            'successMessages' => $this->flashMessenger()->getMessages(),
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
        $materials = $this->getEntityManager()->getRepository('AmaMaterials\Entity\LessonPlanFile')->findLessonPlanFiles($user, '', '', $args);
        $materialsCount = $this->getEntityManager()->getRepository('AmaMaterials\Entity\LessonPlanFile')->findLessonPlanFilesCount($user, '', '', $args);

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
        $viewModel->setTemplate('ama-materials/lesson-plan/index');

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
        $materials = $this->getEntityManager()->getRepository('AmaMaterials\Entity\LessonPlanFile')->findLessonPlanFiles($user, $categories, $category->getId(), $args);
        $materialsCount = $this->getEntityManager()->getRepository('AmaMaterials\Entity\LessonPlanFile')->findLessonPlanFilesCount($user, $categories, $category->getId(), $args);

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

    public function addAction()
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

        $user = $this->identity();
        $file = $this->getEntityManager()->getRepository('AmaMaterials\Entity\File')->findOneBy(array('id' => $this->params('page')));
        if (!$file) {
            return $this->redirect()->toRoute('error');
        }

        $category = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->findOneBy(array('id' => $this->params('id')));
        if (!$category) {
            return $this->redirect()->toRoute('error');
        }

        $lessonPlanFile = $this->getEntityManager()->getRepository('AmaMaterials\Entity\LessonPlanFile')->findOneBy(array('file' => $file, 'category' => $category, 'user' => $user));
        if($lessonPlanFile) {
            return $this->redirect()->toRoute('error');
        }

        if ($request->isPost()) {
            $lessonPlanFile = new LessonPlanFile();
            $lessonPlanFile->setFile($file);
            $lessonPlanFile->setCategory($category);
            $lessonPlanFile->setUser($user);
            $lessonPlanFile->setType($file->getType());
            $this->getEntityManager()->persist($lessonPlanFile);
            $this->getEntityManager()->flush();
            $this->clearCache();
        }

        return new JsonModel(array(
            'success' => true,
        ));
    }

    public function postCommentAction()
    {
        $request = $this->getRequest();
        if (!$request->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        if ($this->params('id')==0) {
            return $this->redirect()->toRoute('error');
        }
        $user = $this->identity();
        $lessonPlanFile = $this->getEntityManager()->getRepository('AmaMaterials\Entity\LessonPlanFile')->findOneBy(array('id' => $this->params('id'), 'user' => $user));
        if (!$lessonPlanFile) {
            return $this->redirect()->toRoute('error');
        }

        if ($request->isPost()) {
            $post = $request->getPost();
            $comment = $post->get('comment');
            $escaper = new Escaper('utf-8');
            $comment = $escaper->escapeHtml($comment);

            $lessonPlanFile->setComment(nl2br($comment));
            $this->getEntityManager()->persist($lessonPlanFile);
            $this->getEntityManager()->flush();
            $this->clearCache();
        }

        return new JsonModel(array(
            'success' => true,
        ));
    }

    public function deleteAction()
    {
        $request = $this->getRequest();
        if (!$request->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        if ($this->params('id')==0) {
            return $this->redirect()->toRoute('error');
        }

        $user = $this->identity();
        $file = $this->getEntityManager()->getRepository('AmaMaterials\Entity\LessonPlanFile')->findOneBy(array('id' => $this->params('id'), 'user' => $user));
        if (!$file) {
            return $this->redirect()->toRoute('error');
        }
        $translator = $this->getServiceLocator()->get('translator');

        //clear file classes
        $fileClasses = $this->getEntityManager()->getRepository('AmaMaterials\Entity\FileClass')->findBy(array('file' => $file));
        foreach($fileClasses as $fileClass) {

            //delete all users cache who are in this class
            $class = $fileClass->getClass();
            $studentsInClass = $class->getStudentsInClass();
            foreach($studentsInClass as $studentInClass) {
                $student = $studentInClass->getStudent();
                if (isset($student) && $student->getFirstStudentUser()){
                    $firstUser = $student->getFirstStudentUser();
                    $firstUser = $firstUser->getUser();
                    $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaLessonPlanCategoriesAndFiles' . $firstUser->getId());
                    $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('userClassesFiles' . $firstUser->getId());
                    $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('userClassesFilesWithCategory' . $firstUser->getId());
                }
            }

            $fileViews = $this->getEntityManager()->getRepository('AmaMaterials\Entity\FileView')->findBy(array('fileClass' => $fileClass));
            foreach($fileViews as $fileView) {
                $this->getEntityManager()->remove($fileView);
            }

            $this->getEntityManager()->remove($fileClass);
        }

        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaFilesSentToClasses' . $file->getFile()->getId(). $user->getId());
        $this->getEntityManager()->remove($file);
        $this->getEntityManager()->flush();
        $this->clearCache();

        $this->flashMessenger()->addMessage($translator->translate("Successfully deleted!"));

        return new JsonModel(array(
            'success' => true,
        ));
    }

    public function sortAction()
    {
        $request = $this->getRequest();
        if (!$request->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }
        $user = $this->identity();
        if ($request->isPost()) {
            $post = $request->getPost();
            $items = $post->get('item');
            $items = array_reverse($items);
            if($items) {
                foreach($items as $key => $item) {
                    $lessonPlanFile = $this->getEntityManager()->getRepository('AmaMaterials\Entity\LessonPlanFile')->findOneBy(array('id' => $item, 'user' => $user));
                    if (!$lessonPlanFile) continue;

                    $lessonPlanFile->setPriority($key);
                    $this->getEntityManager()->persist($lessonPlanFile);
                    $this->getEntityManager()->flush();

                }
                $this->clearCache();
            }
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


    public function clearCache()
    {
        $user = $this->identity();
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaFiles' . $user->getId());
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaFilesCount' . $user->getId());
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaLessonPlanCategoriesAndFiles' . $user->getId());
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaLessonPlanFiles' . $user->getId());
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaLessonPlanFilesCount' . $user->getId());
        $categories = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->findAllCategories();
        foreach($categories as $category){
            $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaFiles' . $category->getId() . $user->getId());
            $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaFilesCount' . $category->getId() . $user->getId());
            $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaLessonPlanFiles' . $category->getId() . $user->getId());
            $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaLessonPlanFilesCount' . $category->getId() . $user->getId());
        }
    }

    public function getEntityManager()
    {
        if (!$this->em) {
            $this->em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        }
        return $this->em;
    }
}

