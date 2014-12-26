<?php

namespace AmaMaterials\Controller;

use AmaMaterials\Entity\File;
use AmaMaterials\Entity\FileClass;
use AmaMaterials\Entity\FileDeleted;
use AmaMaterials\Entity\LessonPlanFile;
use AmaMaterials\Form\AddFilter;
use AmaMaterials\Form\AddForm;
use AmaUsers\Entity\Alert;
use Zend\Escaper\Escaper;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use RdnUpload\Adapter\Local;
use RdnUpload\Container;

class ImageController extends AbstractActionController
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

    public function indexAction()
    {
        return new ViewModel();
    }

    public function addAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        $category = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->find($this->params('cid'));
        if (!$category) {
            return $this->redirect()->toRoute('error');
        }

        $form = new AddForm();
        $translator = $this->getServiceLocator()->get('translator');
        $htmlViewPart = new ViewModel();
        $htmlViewPart->setTerminal(true)
            ->setVariables(array(
                'form' => $form,
                'category' => $category
            ))
            ->setTemplate("ama-materials/image/add");

        $htmlOutput = $this->getServiceLocator()
            ->get('viewrenderer')
            ->render($htmlViewPart);

        return new JsonModel(array(
            'success' => true,
            'html' => $htmlOutput,
            'title' => $translator->translate("Add Image")
        ));
    }

    public function addAjaxAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        $category = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->find($this->params('cid'));
        if (!$category) {
            return $this->redirect()->toRoute('error');
        }

        $translator = $this->getServiceLocator()->get('translator');
        $request = $this->getRequest();
        $message = '';
        $success = true;
        $this->getConfig();
        $user = $this->identity();

        if ($request->isPost()) {

            $form = new AddForm();
            $file = new File();
            $form->setHydrator(new DoctrineHydrator($this->getEntityManager(), 'AmaMaterials\Entity\File'))->setObject($file);
            $form->bind($file);

            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            $form->setInputFilter(new AddFilter($this->getServiceLocator()));
            $form->setData($post);
            $form->getInputFilter()->get('image')->setRequired(true);

            if ( $form->isValid() ) {

                $adapter = new Local($this->config['files_dir'], $this->config['files_public_dir']);
                $uploads = new Container($adapter);
                $id = $uploads->upload($post['image']);

                if ($id) {
                    $form->getData()->setFilename($id);
                }
                if ($category) {
                    $form->getData()->setCategory($category);
                }
                $form->getData()->setUser($user);
                $form->getData()->setType($file::TYPE_IMAGE);
                $this->getEntityManager()->persist($form->getData());
                $this->getEntityManager()->flush();

                //add material to lesson plan
                $lessonPlanFile = new LessonPlanFile();
                $lessonPlanFile->setFile($form->getData());
                $lessonPlanFile->setCategory($category);
                $lessonPlanFile->setUser($user);
                $this->getEntityManager()->persist($lessonPlanFile);
                $this->getEntityManager()->flush();

                $this->clearCache();

                $message = $translator->translate('Successfully added!');
            }
            else {
                $success = false;
                $message = $this->formatMessage()->doFormat($form->getMessages());
            }
        }

        return new JsonModel(array(
            'success' => $success,
            'message' => $message
        ));
    }

    public function postCommentAction()
    {
        $request = $this->getRequest();
        if (!$request->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        if ($this->params('cid')==0) {
            return $this->redirect()->toRoute('error');
        }
        $user = $this->identity();
        $file = $this->getEntityManager()->getRepository('AmaMaterials\Entity\File')->findOneBy(array('id' => $this->params('cid'), 'user' => $user));
        if (!$file) {
            return $this->redirect()->toRoute('error');
        }

        if ($request->isPost()) {
            $post = $request->getPost();
            $comment = $post->get('comment');
            $escaper = new Escaper('utf-8');
            $comment = $escaper->escapeHtml($comment);

            $file->setComment(nl2br($comment));
            $this->getEntityManager()->persist($file);
            $this->getEntityManager()->flush();
            $this->clearCache();
        }

        return new JsonModel(array(
            'success' => true,
        ));
    }

    public function deleteAction()
    {

        $this->getConfig();

        $request = $this->getRequest();
        if (!$request->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        if ($this->params('cid')==0) {
            return $this->redirect()->toRoute('error');
        }

        $user = $this->identity();
        $file = $this->getEntityManager()->getRepository('AmaMaterials\Entity\File')->findOneBy(array('id' => $this->params('cid'), 'user' => $user));
        if (!$file) {
            return $this->redirect()->toRoute('error');
        }

        //delete image thumbnails
        /*if ( $file->getFileName() ) {
            foreach($this->config['htimg']['filters'] as $key => $val ) {
                @unlink(getcwd() . '/public/static/' . $key .'/' . $file->getFileName());
            }
        }*/

        //delete file
        /*$adapter = new Local($this->config['files_dir'], $this->config['files_public_dir']);
        $uploads = new Container($adapter);
        $uploads->delete($file->getFileName());*/

        //delete lesson plans  and files sent to classes
        $lessonPlanFiles = $this->getEntityManager()->getRepository('AmaMaterials\Entity\LessonPlanFile')->findBy(array('file' => $file, 'user' => $user));
        foreach($lessonPlanFiles as $lessonPlanFile) {
            $fileClasses = $this->getEntityManager()->getRepository('AmaMaterials\Entity\FileClass')->findBy(array('file' => $lessonPlanFile, 'sender' => $user));
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

                $this->getEntityManager()->remove($fileClass);
            }
            $this->getEntityManager()->remove($lessonPlanFile);
        }
        $this->getEntityManager()->flush();

        //remove file
        $fileDeleted = new FileDeleted();
        $fileDeleted->setFile($file);
        $fileDeleted->setDeleter($user);
        $this->getEntityManager()->persist($fileDeleted);
        $this->getEntityManager()->flush();
        $this->clearCache();

        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaFilesSentToClasses' . $file->getId()  . $user->getId());
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaMaterialsCount');
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaFilesByType');

        return new JsonModel(array(
            'success' => true,
        ));
    }

    public function sendAction()
    {
        $request = $this->getRequest();
        if (!$request->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        if ($this->params('cid')==0) {
            return $this->redirect()->toRoute('error');
        }
        $user = $this->identity();
        $translator = $this->getServiceLocator()->get('translator');

        if ($request->isPost()) {
            $post = $request->getPost()->toArray();

            $args = array(
                'file' => $this->params('cid'),
                'user' => $user,
            );

            if($this->params('id')>0) {
                $args['category'] = $this->params('id');
            }

            $file = $this->getEntityManager()->getRepository('AmaMaterials\Entity\LessonPlanFile')->findOneBy($args);

            if (!$file) {
                $args = array(
                    'id' => $this->params('cid'),
                    'user' => $user,
                );
                $file = $this->getEntityManager()->getRepository('AmaMaterials\Entity\File')->findOneBy($args);
                if (!$file) {
                    return $this->redirect()->toRoute('error');
                }

                $lessonPlanFile = new LessonPlanFile();
                $lessonPlanFile->setFile($file);
                $lessonPlanFile->setCategory($file->getCategory());
                $lessonPlanFile->setUser($user);
                $lessonPlanFile->setType($file->getType());
                $this->getEntityManager()->persist($lessonPlanFile);
                $this->getEntityManager()->flush();
                $this->clearLessonPlanFilesCache();
                $file = $lessonPlanFile;
            }


            foreach($post['classes'] as $classId) {
                $class = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->findUserClass($user, $classId);
                if (!$class) continue;

                $realFile = $file->getFile();

                $fileClass = new FileClass();
                $fileClass->setClass($class);
                $fileClass->setSender($user);
                $fileClass->setFile($file);
                $this->getEntityManager()->persist($fileClass);
                $this->getEntityManager()->flush();
                $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaFilesSentToClasses' . $realFile->getId()  . $user->getId());


                $sharedA = $translator->translate('shared a file');
                if($file->getType()==$realFile::TYPE_TEST) $sharedA = $translator->translate('shared a test');
                if($file->getType()==$realFile::TYPE_WORKSHEET) $sharedA = $translator->translate('shared a worksheet');

                $message = $translator->translate('Teacher') . ' <strong>'. $user->getFirstName() .' ' . $user->getLastname() . '</strong> ' . $sharedA .'<br>';
                $message .= '<a href="'.$this->url()->fromRoute('home').'#'.$file->getId().'">'.$realFile->getName().'</a> ' . $translator->translate('with you!');


                $studentsInClass = $class->getStudentsInClass();
                foreach($studentsInClass as $studentInClass) {
                    $student = $studentInClass->getStudent();
                    if (isset($student) && $student->getFirstStudentUser()){
                        $firstUser = $student->getFirstStudentUser();
                        $firstUser = $firstUser->getUser();
                        $alert = new Alert();
                        $alert->setUser($firstUser);
                        $alert->setMessage($message);
                        $this->getEntityManager()->persist($alert);
                        $this->getEntityManager()->flush();
                        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaAlerts' . $firstUser->getId());
                        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaLessonPlanCategoriesAndFiles' . $firstUser->getId());
                        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('userClassesFiles' . $firstUser->getId());
                        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('userClassesFilesWithCategory' . $firstUser->getId());
                    }
                }
            }



        }
        $translator = $this->getServiceLocator()->get('translator');
        $this->flashMessenger()->addMessage($translator->translate("Successfully sent!"));
        return new JsonModel(array(
            'success' => true,
        ));
    }

    public function selectAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }
        $limit = 5;
        $page = $this->params('id')>0?$this->params('id'):1;
        $start = ($page - 1) * $limit;
        $page++;
        $user = $this->identity();

        $materials = $this->getEntityManager()->getRepository('AmaMaterials\Entity\File')->findFilesByType($start, $limit);
        foreach($materials as $key => $material) {
            $lessonPlanFile = $this->getEntityManager()->getRepository('AmaMaterials\Entity\LessonPlanFile')->findOneBy(array('user' => $user, 'file' => $material['f_id'], 'category' => $this->params('cid')));
            $materials[$key]['isInLessonPlan'] = ($lessonPlanFile);
        }

        $translator = $this->getServiceLocator()->get('translator');
        $htmlViewPart = new ViewModel();
        $htmlViewPart->setTerminal(true)
            ->setVariables(array(
                'materials' => $materials,
                'page' => $page,
                'loadMore' => ($this->params('id')>0),
                'categoryId' => $this->params('cid')
            ))
            ->setTemplate("ama-materials/image/select");

        $htmlOutput = $this->getServiceLocator()
            ->get('viewrenderer')
            ->render($htmlViewPart);

        return new JsonModel(array(
            'success' => true,
            'html' => $htmlOutput,
            'title' => $translator->translate("Select material")
        ));
    }


    public function clearCache()
    {
        $user = $this->identity();
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaFilesByType');
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaMaterialsCount');
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

    public function clearLessonPlanFilesCache()
    {
        $user = $this->identity();
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaLessonPlanFiles' . $user->getId());
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaLessonPlanFilesCount' . $user->getId());
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaLessonPlanCategoriesAndFiles' . $user->getId());

        $categories = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->findAllCategories();
        foreach($categories as $category){
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

    public function getConfig()
    {
        if (!$this->config) {
            return $this->config = $this->getServiceLocator()->get('config');
        }
        return $this->config;
    }

}

