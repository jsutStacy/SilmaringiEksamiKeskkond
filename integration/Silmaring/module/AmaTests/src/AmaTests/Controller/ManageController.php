<?php
namespace AmaTests\Controller;


use AmaMaterials\Entity\File;
use AmaMaterials\Entity\FileDeleted;
use AmaMaterials\Entity\LessonPlanFile;
use AmaTests\Entity\Answer;
use AmaTests\Entity\Question;
use AmaTests\Entity\QuestionImage;
use AmaTests\Entity\TempImage;
use AmaTests\Entity\Test;
use AmaTests\Entity\UserTestResult;
use AmaTests\Form\AddFilter;
use AmaTests\Form\AddForm;
use AmaTests\Form\AddTempImageFilter;
use AmaTests\Form\AddTempImageForm;
use AmaUsers\Entity\Alert;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Escaper\Escaper;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use RdnUpload\Adapter\Local;
use RdnUpload\Container;

class ManageController extends AbstractActionController
{

    /**
     * Entity Manager
     * @var $em
     */
    protected $em;

    /**
     * Main config
     * @var $config
     */
    protected $config;

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

        $form = new AddForm($this->getEntityManager());
        $imageThumbForm  = new AddTempImageForm();
        $translator = $this->getServiceLocator()->get('translator');
        $htmlViewPart = new ViewModel();
        $htmlViewPart->setTerminal(true)
            ->setVariables(array(
                'form' => $form,
                'category' => $category,
                'imageThumbForm' => $imageThumbForm
            ))
            ->setTemplate("ama-tests/manage/add");

        $htmlOutput = $this->getServiceLocator()
            ->get('viewrenderer')
            ->render($htmlViewPart);

        return new JsonModel(array(
            'success' => true,
            'html' => $htmlOutput,
            'title' => $translator->translate("Add Test")
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
        $messageType = 'SUCCESS_SAVED';
        $this->getConfig();
        $user = $this->identity();

        if ($request->isPost()) {
            $form = new AddForm($this->getEntityManager());
            $test = new Test();
            $form->setHydrator(new DoctrineHydrator($this->getEntityManager(), 'AmaTests\Entity\Test'))->setObject(new Test());
            $form->bind($test);

            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            $form->setInputFilter(new AddFilter($this->getServiceLocator()));
            $form->setData($post);

            if ( $form->isValid() ) {
                if ($category) {
                    $form->getData()->setCategory($category);
                }
                $form->getData()->setUser($user);

                //print_r($post['question_element']);
                //exit;
                //add questions and answers
                $order = 0;
                foreach($post['question_element'] as $qnr => $q) {

                    $question = new Question();
                    $question->setOrder($order);
                    $question->setQuestion($this->cleanInputs()->clean($q['question']));
                    $question->setPoints($this->cleanInputs()->clean($q['points']));
                    $question->setAnswerType($this->cleanInputs()->clean($q['answerType']));

                    foreach($q['answer_option_element'] as $a) {ue;

                        $answer = new Answer();

                        if($a['rightAnswer']) $answer->setIsRight((int)$a['rightAnswer']);
                        else $answer->setIsRight((int)$a['rightRange']);

                        $answer->setTest($form->getData());
                        $answer->setOption($this->cleanInputs()->clean($a['option']));
                        $answer->setOptionTwo($this->cleanInputs()->clean($a['optionTwo']));
                        $question->addAnswer($answer);
                    }

                    if(!empty($q['words']) && empty($q['answer_option_element'])) {
                        $answer = new Answer();
                        if(!empty($q['words'])) {
                            $answer->setIsRight(1);
                        }
                        $answer->setTest($form->getData());
                        $answer->setMustContainWords((int)$q['mustContainWords']);
                        $answer->setWords($this->cleanInputs()->clean($q['words']));
                        $question->addAnswer($answer);
                    }

                    //add images to question
                    $imageFiles = array();
                    foreach($q['images'] as $image) {
                        //make sure we dont use double images
                        if(in_array($image, $imageFiles)) continue;
                        $imageFiles[] = $image;

                        $tempImage = $this->getEntityManager()->getRepository('AmaTests\Entity\TempImage')->find($image);
                        if(!$tempImage) continue;

                        $questionImage = new QuestionImage();
                        $questionImage->setTest($form->getData());
                        $questionImage->setFilename($tempImage->getFilename());
                        $question->addImage($questionImage);

                        $this->getEntityManager()->remove($tempImage);
                    }

                    $form->getData()->addQuestion($question);
                    $order++;
                }

                $this->getEntityManager()->persist($form->getData());
                $this->getEntityManager()->flush();
                //exit;

                $file = new File();
                $file->setName($form->getData()->getName());
                $file->setDescription($form->getData()->getDescription());
                $file->setType($file::TYPE_TEST);
                $file->setUser($user);
                $file->setTest($form->getData());
                $file->setCategory($category);
                $this->getEntityManager()->persist($file);
                $this->getEntityManager()->flush();

                //add material to lesson plan
                $lessonPlanFile = new LessonPlanFile();
                $lessonPlanFile->setFile($file);
                $lessonPlanFile->setCategory($category);
                $lessonPlanFile->setUser($user);
                $lessonPlanFile->setType($file::TYPE_TEST);
                $this->getEntityManager()->persist($lessonPlanFile);
                $this->getEntityManager()->flush();

                $this->clearCache();
                $this->clearTestCache($form->getData()->getId());

                $message = $translator->translate('Successfully added!');
            }
            else {
                $messages = $form->getMessages();

                if($this->isPointsError($messages))
                    $messageType = 'ERROR_POINTS_EMPTY';

                $success = false;
                $message = $this->formatMessage()->doFormat($messages);
            }
        }

        return new JsonModel(array(
            'success' => $success,
            'message' => $message,
            'message_type' => $messageType
        ));
    }

    public function createImageThumbAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        $translator = $this->getServiceLocator()->get('translator');
        $request = $this->getRequest();
        $message = '';
        $success = true;
        $this->getConfig();
        $user = $this->identity();
        $image = '';

        if ($request->isPost()) {
            $form = new AddTempImageForm();
            $file = new TempImage();
            $form->setHydrator(new DoctrineHydrator($this->getEntityManager(), 'AmaTests\Entity\TempImage'))->setObject(new TempImage());
            $form->bind($file);

            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            $form->setInputFilter(new AddTempImageFilter($this->getServiceLocator()));
            $form->setData($post);
            if ( $form->isValid() ) {
                $adapter = new Local($this->config['files_dir'], $this->config['files_public_dir']);
                $uploads = new Container($adapter);

                $id = $uploads->upload($post['image']);

                if ($id) {
                    $form->getData()->setFilename($id);
                }

                $form->getData()->setUser($user);
                $this->getEntityManager()->persist($form->getData());
                $this->getEntityManager()->flush();

                if ($id) {
                    //return thumb
                    $htmlViewPart = new ViewModel();
                    $htmlViewPart->setTerminal(true)
                        ->setVariables(array(
                            'filename' => $form->getData()->getFilename(),
                            'imageId' => $form->getData()->getId(),
                            'questionNr' => $post['questionNr']
                        ))
                        ->setTemplate("ama-tests/partial/image-thumb");

                    $image = $this->getServiceLocator()
                        ->get('viewrenderer')
                        ->render($htmlViewPart);
                }

                $message = $translator->translate('Successfully added!');
            }
            else {
                $success = false;
                $message = $this->formatMessage()->doFormat($form->getMessages());
            }
        }

        return new JsonModel(array(
            'success' => $success,
            'message' => $message,
            'image' => $image,
            'image_id' => $form->getData()->getId()
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

        $test = $this->getEntityManager()->getRepository('AmaTests\Entity\Test')->findOneBy(array('id' => $file->getTest(), 'user' => $user));
        if (!$test) {
            return $this->redirect()->toRoute('error');
        }

        if ($request->isPost()) {
            $post = $request->getPost();
            $comment = $post->get('comment');
            $escaper = new Escaper('utf-8');
            $comment = $escaper->escapeHtml($comment);

            $file->setComment(nl2br($comment));
            $test->setComment(nl2br($comment));
            $this->getEntityManager()->persist($file);
            $this->getEntityManager()->persist($test);
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


    public function deleteQuestionAction()
    {
        $this->getConfig();

        $request = $this->getRequest();
        if (!$request->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        if ($this->params('cid')==0) {
            return new JsonModel(array(
                'success' => false,
            ));
        }

        if ($this->params('id')==0) {
            return new JsonModel(array(
                'success' => false,
            ));
        }

        $user = $this->identity();
        $test = $this->getEntityManager()->getRepository('AmaTests\Entity\Test')->findOneBy(array('id' => $this->params('id'), 'user' => $user));
        if (!$test) {
            return new JsonModel(array(
                'success' => false,
            ));
        }

        $question = $this->getEntityManager()->getRepository('AmaTests\Entity\Question')->findOneBy(array('id' => $this->params('cid'), 'test' => $test));
        if (!$question) {
            return new JsonModel(array(
                'success' => false,
            ));
        }


        //Delete question images
        $images = $this->getEntityManager()->getRepository('AmaTests\Entity\QuestionImage')->findOneBy(array('question' => $question));
        if($images) {
            foreach($images as $file) {
                //delete image thumbnails
                if ( $file->getFileName() ) {
                    foreach($this->config['htimg']['filters'] as $key => $val ) {
                        @unlink(getcwd() . '/public/static/' . $key .'/' . $file->getFilename());
                    }
                }

                //delete file
                $adapter = new Local($this->config['files_dir'], $this->config['files_public_dir']);
                $uploads = new Container($adapter);
                $uploads->delete($file->getFilename());

                $this->getEntityManager()->remove($file);
            }
        }


        $this->getEntityManager()->remove($question);
        $this->getEntityManager()->flush();

        $this->clearTestCache($test->getId());

        $this->clearCache();
        $translator = $this->getServiceLocator()->get('translator');
        return new JsonModel(array(
            'success' => true,
            'message' => $translator->translate("Successfully removed!")
        ));
    }

    public function deleteQuestionImageAction()
    {
        $this->getConfig();

        $request = $this->getRequest();
        if (!$request->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        if ($this->params('cid')==0) {
            return new JsonModel(array(
                'success' => false,
            ));
        }

        if ($this->params('id')==0) {
            return new JsonModel(array(
                'success' => false,
            ));
        }

        $question = $this->getEntityManager()->getRepository('AmaTests\Entity\Question')->findOneBy(array('id' => $this->params('cid')));
        if (!$question) {
            return new JsonModel(array(
                'success' => false,
            ));
        }

        $user = $this->identity();
        $test = $this->getEntityManager()->getRepository('AmaTests\Entity\Test')->findOneBy(array('id' => $question->getTest(), 'user' => $user));
        if (!$test) {
            return new JsonModel(array(
                'success' => false,
            ));
        }


        $file = $this->getEntityManager()->getRepository('AmaTests\Entity\QuestionImage')->findOneBy(array('id' => $this->params('id'), 'question' => $question));
        if(!$file) {
            return new JsonModel(array(
                'success' => false,
            ));
        }


        //delete image thumbnails
        if ( $file->getFileName() ) {
            foreach($this->config['htimg']['filters'] as $key => $val ) {
                @unlink(getcwd() . '/public/static/' . $key .'/' . $file->getFilename());
            }
        }

        //delete file
        $adapter = new Local($this->config['files_dir'], $this->config['files_public_dir']);
        $uploads = new Container($adapter);
        $uploads->delete($file->getFilename());

        $this->getEntityManager()->remove($file);
        $this->getEntityManager()->flush();

        $this->clearTestCache($test->getId());

        $translator = $this->getServiceLocator()->get('translator');
        return new JsonModel(array(
            'success' => true,
            'message' => $translator->translate("Successfully removed!")
        ));
    }

    public function deleteTempQuestionImageAction()
    {
        $this->getConfig();

        $request = $this->getRequest();
        if (!$request->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        if ($this->params('cid')==0) {
            return new JsonModel(array(
                'success' => false,
            ));
        }

        $user = $this->identity();
        $file = $this->getEntityManager()->getRepository('AmaTests\Entity\TempImage')->findOneBy(array('id' => $this->params('cid'), 'user' => $user));
        if(!$file) {
            return new JsonModel(array(
                'success' => false,
            ));
        }

        //delete image thumbnails
        if ( $file->getFileName() ) {
            foreach($this->config['htimg']['filters'] as $key => $val ) {
                @unlink(getcwd() . '/public/static/' . $key .'/' . $file->getFilename());
            }
        }

        //delete file
        $adapter = new Local($this->config['files_dir'], $this->config['files_public_dir']);
        $uploads = new Container($adapter);
        $uploads->delete($file->getFilename());

        $this->getEntityManager()->remove($file);
        $this->getEntityManager()->flush();

        return new JsonModel(array(
            'success' => true,
        ));
    }


    public function editAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        /*if ($this->params('cid')==0) {
            return new JsonModel(array(
                'success' => false,
            ));
        }*/

        if ($this->params('id')==0) {
            return new JsonModel(array(
                'success' => false,
            ));
        }

       /* $category = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->find($this->params('cid'));
        if (!$category) {
            return $this->redirect()->toRoute('error');
        }*/

        $user = $this->identity();
        $file = $this->getEntityManager()->getRepository('AmaMaterials\Entity\File')->findOneBy(array('id' => $this->params('id'), 'user' => $user));
        if (!$file) {
            return $this->redirect()->toRoute('error');
        }
        $test = $this->getEntityManager()->getRepository('AmaTests\Entity\Test')->findOneBy(array('id' => $file->getTest(), 'user' => $user));
        if (!$test) {
            return $this->redirect()->toRoute('error');
        }

        $form = new AddForm($this->getEntityManager());
        $imageThumbForm  = new AddTempImageForm();
        $translator = $this->getServiceLocator()->get('translator');
        $htmlViewPart = new ViewModel();
        $htmlViewPart->setTerminal(true)
            ->setVariables(array(
                'form' => $form,
                'test' => $test,
                'file' => $file,
                //'category' => $category,
                'imageThumbForm' => $imageThumbForm
            ))
            ->setTemplate("ama-tests/manage/edit");

        $htmlOutput = $this->getServiceLocator()
            ->get('viewrenderer')
            ->render($htmlViewPart);

        return new JsonModel(array(
            'success' => true,
            'html' => $htmlOutput,
            'title' => $translator->translate("Edit Test")
        ));
    }

    public function editAjaxAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        if ($this->params('cid')==0) {
            return new JsonModel(array(
                'success' => false
            ));
        }

        if ($this->params('id')==0) {
            return new JsonModel(array(
                'success' => false
            ));
        }

        $user = $this->identity();
        $file = $this->getEntityManager()->getRepository('AmaMaterials\Entity\File')->findOneBy(array('id' => $this->params('cid'), 'user' => $user));
        if (!$file) {
            return new JsonModel(array(
                'success' => false
            ));
        }

        $test = $this->getEntityManager()->getRepository('AmaTests\Entity\Test')->findOneBy(array('id' => $this->params('id'), 'user' => $user));
        if (!$test) {
            return new JsonModel(array(
                'success' => false
            ));
        }

        $translator = $this->getServiceLocator()->get('translator');
        $request = $this->getRequest();
        $message = '';
        $messageType  = 'SUCCESS_SAVED';
        $success = true;
        $this->getConfig();

        if ($request->isPost()) {
            $form = new AddForm($this->getEntityManager());
            $form->setHydrator(new DoctrineHydrator($this->getEntityManager(), 'AmaTests\Entity\Test'))->setObject($test);
            $form->bind($test);

            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            $form->setInputFilter(new AddFilter($this->getServiceLocator()));
            $form->setData($post);

            if ( $form->isValid() ) {

               //print_r($post['question_element']);
                //exit;

                //add questions and answers
                $order = 0;
                $dateModfied = new \DateTime();
                foreach($post['question_element'] as $qnr => $q) {
                    //if question exists
                    if(!empty($q['questionId'])) {
                        $question = $this->getEntityManager()->getRepository('AmaTests\Entity\Question')->findOneBy(array('id' => $q['questionId'], 'test' => $test));
                        //delete all question answers
                        $answers = $this->getEntityManager()->getRepository('AmaTests\Entity\Answer')->findBy(array('question' => $question));
                        if($answers) {
                            foreach($answers as $answer) {
                                 $this->getEntityManager()->remove($answer);
                                 $this->getEntityManager()->flush();
                            }
                        }
                    }
                    else {
                         $question = new Question();
                    }

                    $question->setOrder($order);
                    $question->setQuestion($this->cleanInputs()->clean($q['question']));
                    $question->setPoints($this->cleanInputs()->clean($q['points']));
                    $question->setAnswerType($this->cleanInputs()->clean($q['answerType']));
                    //$allreadySent = false;
                    foreach($q['answer_option_element'] as $a) {

                        //make sure we dont put multiple answers when using words answer type
                        //if($allreadySent && !empty($q['words'])) continue;
                        //$allreadySent = true;

                        $answer = new Answer();

                        if($a['rightAnswer']) $answer->setIsRight((int)$a['rightAnswer']);
                        else $answer->setIsRight((int)$a['rightRange']);

                        $answer->setTest($form->getData());
                        $answer->setOption($this->cleanInputs()->clean($a['option']));
                        $answer->setOptionTwo($this->cleanInputs()->clean($a['optionTwo']));
                        $answer->setDateModified(new \DateTime());
                        $question->addAnswer($answer);
                    }

                    if(!empty($q['words']) && empty($q['answer_option_element'])) {
                        $answer = new Answer();
                        if(!empty($q['words'])) {
                            $answer->setIsRight(1);
                        }
                        $answer->setTest($form->getData());
                        $answer->setMustContainWords((int)$q['mustContainWords']);
                        $answer->setWords($this->cleanInputs()->clean($q['words']));
                        $answer->setDateModified(new \DateTime());
                        $question->addAnswer($answer);
                    }

                    //add images to question
                    $imageFiles = array();
                    foreach($q['images'] as $image) {
                        //make sure we dont use double images
                        if(in_array($image, $imageFiles)) continue;
                        $imageFiles[] = $image;

                        $tempImage = $this->getEntityManager()->getRepository('AmaTests\Entity\TempImage')->find($image);
                        if(!$tempImage) continue;

                        $questionImage = new QuestionImage();
                        $questionImage->setTest($form->getData());
                        $questionImage->setFilename($tempImage->getFilename());
                        $question->addImage($questionImage);

                        $this->getEntityManager()->remove($tempImage);
                    }

                    if(!empty($q['questionId'])) {
                         $this->getEntityManager()->persist($question);
                        $this->getEntityManager()->flush();
                    }
                    else {
                        $form->getData()->addQuestion($question);
                    }
                    $order++;
                }

                $this->getEntityManager()->persist($form->getData());

                $file->setName($form->getData()->getName());
                $file->setDescription($form->getData()->getDescription());
                $this->getEntityManager()->persist($file);
                $this->getEntityManager()->flush();

                $this->clearCache();
                $this->clearTestCache($test->getId());

                $message = $translator->translate('Successfully updated!');
            }
            else {
                $messages = $form->getMessages();

                if($this->isPointsError($messages))
                    $messageType = 'ERROR_POINTS_EMPTY';

                $success = false;
                $message = $this->formatMessage()->doFormat($messages);
            }
        }

        return new JsonModel(array(
            'success' => $success,
            'message' => $message,
            'message_type' => $messageType
        ));
    }

    public function noEditAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        /*if ($this->params('cid')==0) {
            return new JsonModel(array(
                'success' => false,
            ));
        }*/

        if ($this->params('id')==0) {
            return new JsonModel(array(
                'success' => false,
            ));
        }

        /* $category = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->find($this->params('cid'));
         if (!$category) {
             return $this->redirect()->toRoute('error');
         }*/

        $user = $this->identity();
        $file = $this->getEntityManager()->getRepository('AmaMaterials\Entity\File')->findOneBy(array('id' => $this->params('id')));
        if (!$file) {
            return $this->redirect()->toRoute('error');
        }
        $test = $this->getEntityManager()->getRepository('AmaTests\Entity\Test')->findOneBy(array('id' => $file->getTest()));
        if (!$test) {
            return $this->redirect()->toRoute('error');
        }

        $form = new AddForm($this->getEntityManager());
        $imageThumbForm  = new AddTempImageForm();
        $translator = $this->getServiceLocator()->get('translator');
        $htmlViewPart = new ViewModel();
        $htmlViewPart->setTerminal(true)
            ->setVariables(array(
                'form' => $form,
                'test' => $test,
                'file' => $file,
                'user' => $user,
                //'category' => $category,
                'imageThumbForm' => $imageThumbForm
            ))
            ->setTemplate("ama-tests/manage/no-edit");

        $htmlOutput = $this->getServiceLocator()
            ->get('viewrenderer')
            ->render($htmlViewPart);

        return new JsonModel(array(
            'success' => true,
            'html' => $htmlOutput,
            'title' => $translator->translate("Cannot edit test")
        ));
    }

    public function statisticsAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        if ($this->params('cid')==0) {
            return new JsonModel(array(
                'success' => false,
            ));
        }

        if ($this->params('id')==0) {
            return new JsonModel(array(
                'success' => false,
            ));
        }

        $user = $this->identity();
        $test = $this->getEntityManager()->getRepository('AmaTests\Entity\Test')->findTestStatistics($this->params('cid'), $this->params('id'), $user->getId());
        if(!$test) {
            return new JsonModel(array(
                'success' => false,
            ));
        }

        $test['averagePoints'] = ceil($test['totalRightPoints']/$test['finishedUsersCount']);
        $test['percentOfMax'] = ceil(($test['averagePoints']/$test['totalPoints'])*100);

        $test['questions'] = $this->getEntityManager()->getRepository('AmaTests\Entity\Test')->findTestStatisticsQuestions($test['t_id'], $this->params('cid'), $this->params('id'), $user);
        foreach($test['questions'] as $key => $question) {
            $test['questions'][$key]['answers'] = array();
        }
        //var_dump($test['questions']);

        $translator = $this->getServiceLocator()->get('translator');
        $htmlViewPart = new ViewModel();
        $htmlViewPart->setTerminal(true)
            ->setVariables(array(
                'test' => $test,
                'lessonPlanId' => $this->params('cid'),
                'fileClassId' => $this->params('id')
            ))
            ->setTemplate("ama-tests/manage/statistics");

        $htmlOutput = $this->getServiceLocator()
            ->get('viewrenderer')
            ->render($htmlViewPart);

        return new JsonModel(array(
            'success' => true,
            'html' => $htmlOutput,
            'title' => $translator->translate("Test overview")
        ));
    }

    public function viewAnswersAction()
    {
        $request = $this->getRequest();
        if (!$request->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        if ($this->params('cid')==0) {
            return new JsonModel(array(
                'success' => false,
            ));
        }

        if ($this->params('id')==0) {
            return new JsonModel(array(
                'success' => false,
            ));
        }

        if(!$request->isPost()) {
            return new JsonModel(array(
                'success' => false,
            ));
        }

        $post = $request->getPost();
        if($post->get('question_id')==0) {
            return new JsonModel(array(
                'success' => false,
            ));
        }

        $translator = $this->getServiceLocator()->get('translator');
        $user = $this->identity();
        $test = $this->getEntityManager()->getRepository('AmaTests\Entity\Test')->findTestStatistics($this->params('cid'), $this->params('id'), $user->getId());
        if(!$test) {
            return new JsonModel(array(
                'success' => false,
                'html' => $translator->translate("No test found!")
            ));
        }

        $answers = $this->getEntityManager()->getRepository('AmaTests\Entity\Test')->findTestStatisticsQuestionAnswers($this->params('cid'), $this->params('id'), $user->getId(), $post->get('question_id'));
        if(!$answers) {
            return new JsonModel(array(
                'success' => false,
                'html' => $translator->translate("No answers found!")
            ));
        }

        $htmlViewPart = new ViewModel();
        $htmlViewPart->setTerminal(true)
            ->setVariables(array(
                'answers' => $answers,
                'test' => $test,
                'lessonPlanId' => $this->params('cid'),
                'fileClassId' => $this->params('id')
            ))
            ->setTemplate("ama-tests/manage/statistics-answers");

        $htmlOutput = $this->getServiceLocator()
            ->get('viewrenderer')
            ->render($htmlViewPart);

        return new JsonModel(array(
            'success' => true,
            'html' => $htmlOutput,
        ));
    }

    public function valuationAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        if ($this->params('cid')==0) {
            return new JsonModel(array(
                'success' => false,
            ));
        }

        if ($this->params('id')==0) {
            return new JsonModel(array(
                'success' => false,
            ));
        }

        $user = $this->identity();
        $test = $this->getEntityManager()->getRepository('AmaTests\Entity\Test')->findTestStatistics($this->params('cid'), $this->params('id'), $user->getId());
        if(!$test) {
            return new JsonModel(array(
                'success' => false,
            ));
        }

        $test['averagePoints'] = ceil($test['totalRightPoints']/$test['finishedUsersCount']);
        $test['percentOfMax'] = ceil(($test['averagePoints']/$test['totalPoints'])*100);

        $test['questions'] = $this->getEntityManager()->getRepository('AmaTests\Entity\Test')->findTestStatisticsQuestions($test['t_id'], $this->params('cid'), $this->params('id'), $user);
        $test['answers'] = array();

        foreach($test['questions'] as $key => $question) {
            $answers = $this->getEntityManager()->getRepository('AmaTests\Entity\Test')->findTestStatisticsQuestionAnswers($this->params('cid'), $this->params('id'), $user->getId(), $question['q_id']);
            foreach($answers as $answer) {
                $answer['points'] = $question['q_points'];
                //$test['answers'][$answer['s_firstName'] . ' ' . $answer['s_lastname']]['question_points'] = $question['q_points'];
                $test['answers'][$answer['s_firstName'] . ' ' . $answer['s_lastname']]['student'] = $answer['u_id'];
                $test['answers'][$answer['s_firstName'] . ' ' . $answer['s_lastname']]['answers'][$question['q_id']][] = $answer;
            }
        }

        $userTestResult = $this->getEntityManager()->getRepository('AmaTests\Entity\UserTestResult')->findOneBy(
            array(
                'sender' => $user,
                'test' => $test['t_id'],
                'lessonPlan' => $test['lpf_id'],
                'fileClass' => $test['fc_id']
            ));
        //var_dump($user->getId(), $test['t_id'], $test['lpf_id'], $test['fc_id']);
        $translator = $this->getServiceLocator()->get('translator');
        $htmlViewPart = new ViewModel();
        $htmlViewPart->setTerminal(true)
            ->setVariables(array(
                'test' => $test,
                'lessonPlanId' => $this->params('cid'),
                'fileClassId' => $this->params('id'),
                'allreadySent' => $userTestResult
            ))
            ->setTemplate("ama-tests/manage/valuation");

        $htmlOutput = $this->getServiceLocator()
            ->get('viewrenderer')
            ->render($htmlViewPart);

        return new JsonModel(array(
            'success' => true,
            'html' => $htmlOutput,
            'title' => $translator->translate("Test valuation")
        ));
    }

    public function saveValuationAction()
    {
        $request = $this->getRequest();
        $translator = $this->getServiceLocator()->get('translator');
        $message = $translator->translate("No students have finished the test!");
        if (!$request->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        if ($this->params('cid')==0) {
            return new JsonModel(array(
                'success' => false,
            ));
        }

        if ($this->params('id')==0) {
            return new JsonModel(array(
                'success' => false,
            ));
        }

        $user = $this->identity();
        $test = $this->getEntityManager()->getRepository('AmaTests\Entity\Test')->findTestStatistics($this->params('cid'), $this->params('id'), $user->getId());
        if(!$test) {
            return new JsonModel(array(
                'success' => false,
                'message' => $message
            ));
        }

        if(!$request->isPost()) {
            return new JsonModel(array(
                'success' => false,
                'message' => $message
            ));
        }

        $post = $request->getPost()->toArray();
        $success = false;
        $fileClass = $this->getEntityManager()->getRepository('AmaMaterials\Entity\FileClass')->find($this->params('id'));
        $fileClassSender = $fileClass->getSender();
        $lastQuestionId = '';
        foreach($post['gradeComments'] as  $aid => $comment) {
            $comment  = $this->cleanInputs()->clean($comment);
            $grade    = $this->cleanInputs()->clean($post['grades'][$aid]);
            $aid      = $this->cleanInputs()->clean($aid);

            if(empty($aid)) continue;

            $userAnswer = $this->getEntityManager()->getRepository('AmaTests\Entity\UserTestAnswer')->find($aid);
            if($userAnswer->getTest()->getId()!=$test['t_id']) continue;

            $success = true;
            $userAnswer->setComment($comment);
            $userAnswer->setPoints($grade);

            $this->getEntityManager()->persist($userAnswer);

            //make sure we run clean cache only once per category
            $answerQuestion = $userAnswer->getAnsweredQuestion();

            $answerQuestion->setPoints($grade);
            $this->getEntityManager()->persist($answerQuestion);

            $question = $userAnswer->getQuestion();
            if($lastQuestionId!=$question->getId()) {
            $lastQuestionId = $question->getId();
            $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaTestStatisticsQuestionAnswers' . $this->params('cid') . $this->params('id'). $lastQuestionId. $fileClassSender->getId());
            }

            $message = $translator->translate('Successfully saved!');
        }
        $this->getEntityManager()->flush();

        return new JsonModel(array(
            'success' => $success,
            'message' => $message
        ));
    }

    public function sendValuationAction()
    {
        $request = $this->getRequest();
        $translator = $this->getServiceLocator()->get('translator');
        $message = $translator->translate("No students have finished the test!");
        if (!$request->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        if ($this->params('cid')==0) {
            return new JsonModel(array(
                'success' => false,
            ));
        }

        if ($this->params('id')==0) {
            return new JsonModel(array(
                'success' => false,
            ));
        }

        $user = $this->identity();
        $test = $this->getEntityManager()->getRepository('AmaTests\Entity\Test')->findTestStatistics($this->params('cid'), $this->params('id'), $user->getId());
        if(!$test) {
            return new JsonModel(array(
                'success' => false,
                'message' => $message
            ));
        }

        if(!$request->isPost()) {
            return new JsonModel(array(
                'success' => false,
                'message' => $message
            ));
        }

        $post = $request->getPost()->toArray();
        $success = false;
        $fileClass = $this->getEntityManager()->getRepository('AmaMaterials\Entity\FileClass')->find($this->params('id'));
        $lessonPlanFile = $this->getEntityManager()->getRepository('AmaMaterials\Entity\LessonPlanFile')->find($test['lpf_id']);
        $realTest = $this->getEntityManager()->getRepository('AmaTests\Entity\Test')->find($test['t_id']);

        $fileClassSender = $fileClass->getSender();
        $lastQuestionId = '';
        foreach($post['gradeComments'] as  $aid => $comment) {
            $comment  = $this->cleanInputs()->clean($comment);
            $grade    = $this->cleanInputs()->clean($post['grades'][$aid]);
            $aid      = $this->cleanInputs()->clean($aid);

            if(empty($aid)) continue;

            $userAnswer = $this->getEntityManager()->getRepository('AmaTests\Entity\UserTestAnswer')->find($aid);
            if($userAnswer->getTest()->getId()!=$test['t_id']) continue;

            $success = true;
            $userAnswer->setComment($comment);
            $userAnswer->setPoints($grade);

            $this->getEntityManager()->persist($userAnswer);

            //make sure we run clean cache only once per category
            $answerQuestion = $userAnswer->getAnsweredQuestion();

            $answerQuestion->setPoints($grade);
            $this->getEntityManager()->persist($answerQuestion);

            $question = $userAnswer->getQuestion();
            if($lastQuestionId!=$question->getId()) {
                $lastQuestionId = $question->getId();
                $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaTestStatisticsQuestionAnswers' . $this->params('cid') . $this->params('id'). $lastQuestionId. $fileClassSender->getId());
            }
        }
        $this->getEntityManager()->flush();

        foreach($post['students'] as $userId) {
            $userId  = $this->cleanInputs()->clean($userId);
            if(empty($userId)) continue;

            $userTestResult = $this->getEntityManager()->getRepository('AmaTests\Entity\UserTestResult')->findOneBy(
                array(
                    'user' => $userId,
                    'sender' => $user,
                    'test' => $test['t_id'],
                    'lessonPlan' => $lessonPlanFile,
                    'fileClass' => $fileClass
                ));

            $success = true;
            $studentUser = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->find($userId);

            $message = $translator->translate('Teacher') . ' <strong>'. $user->getFirstName() .' ' . $user->getLastname() . '</strong> ' . $translator->translate("has sent you results for test") .' ';
            $message .= '<a href="javascript:;" onclick="silmaring.openInModal(\''.$this->url()->fromRoute('solveTests', array('action' => 'solved', 'id' => $lessonPlanFile->getId(), 'fid' => $fileClass->getId())).'\', \'modal-lg\');">' .$realTest->getName() .'</a> ';

            $alert = new Alert();
            $alert->setUser($studentUser);
            $alert->setMessage($message);
            $this->getEntityManager()->persist($alert);

            if($userTestResult) continue;

            $userTestResult = new UserTestResult();
            $userTestResult->setUser($studentUser);
            $userTestResult->setSender($user);
            $userTestResult->setFileClass($fileClass);
            $userTestResult->setTest($realTest);
            $userTestResult->setLessonPlan($lessonPlanFile);
            $this->getEntityManager()->persist($userTestResult);

        }

        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaSolveTest' . $test['lpf_id']);
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaSolveTest' . $test['fc_id']);

        if($success) $message = $translator->translate('Successfully sent!');
        $this->getEntityManager()->flush();

        return new JsonModel(array(
            'success' => $success,
            'message' => $message
        ));
    }

    public function clearCache()
    {
        $user = $this->identity();
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaFilesByType');
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaMaterialsCount');
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaTests' . $user->getId());
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaTestsCount' . $user->getId());
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaLessonPlanCategoriesAndFiles' . $user->getId());

        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaLessonPlanFiles' . $user->getId());
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaLessonPlanFilesCount' . $user->getId());

        $categories = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->findAllCategories();
        foreach($categories as $category){
            $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaTests' . $category->getId() . $user->getId());
            $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaTestsCount' . $category->getId() . $user->getId());

            $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaLessonPlanFiles' . $category->getId() . $user->getId());
            $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaLessonPlanFilesCount' . $category->getId() . $user->getId());
        }
    }

    public function clearTestCache($testId)
    {
        $questions = $this->getEntityManager()->getRepository('AmaTests\Entity\Test')->findQuestionsByTestId($testId);
        foreach($questions as $question) {
            $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaSolveTestAnswers' . $question['q_id'] . $testId);
            $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaSolveTestRightAnswers' . $question['q_id'] . $testId);
            $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaSolveTestImages' . $question['q_id'] . $testId);
        }
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaSolveTest' . $testId);
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaSolveTestQuestions' . $testId);
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

    public function isPointsError($messages)
    {
        if(!is_array($messages)) return false;
        if(!isset($messages['question_element'])) return false;
        foreach($messages['question_element'] as $row) {
            if(isset($row['points']))  return true;
        }

        return false;
    }
}