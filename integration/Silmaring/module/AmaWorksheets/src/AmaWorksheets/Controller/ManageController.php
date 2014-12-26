<?php
namespace AmaWorksheets\Controller;


use AmaMaterials\Entity\File;
use AmaMaterials\Entity\FileDeleted;
use AmaMaterials\Entity\LessonPlanFile;
use AmaWorksheets\Entity\Answer;
use AmaWorksheets\Entity\Question;
use AmaWorksheets\Entity\QuestionImage;
use AmaWorksheets\Entity\TempImage;
use AmaWorksheets\Entity\Worksheet;
use AmaWorksheets\Entity\UserWorksheetResult;
use AmaWorksheets\Form\AddFilter;
use AmaWorksheets\Form\AddForm;
use AmaWorksheets\Form\AddTempImageFilter;
use AmaWorksheets\Form\AddTempImageForm;
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
            ->setTemplate("ama-worksheets/manage/add");

        $htmlOutput = $this->getServiceLocator()
            ->get('viewrenderer')
            ->render($htmlViewPart);

        return new JsonModel(array(
            'success' => true,
            'html' => $htmlOutput,
            'title' => $translator->translate("Add Worksheet")
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
        $messageType = 'SUCCESS_SAVED';
        $success = true;
        $this->getConfig();
        $user = $this->identity();

        if ($request->isPost()) {
            $form = new AddForm($this->getEntityManager());
            $worksheet = new Worksheet();
            $form->setHydrator(new DoctrineHydrator($this->getEntityManager(), 'AmaWorksheets\Entity\Worksheet'))->setObject(new Worksheet());
            $form->bind($worksheet);

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

                        $answer->setWorksheet($form->getData());
                        $answer->setOption($this->cleanInputs()->clean($a['option']));
                        $answer->setOptionTwo($this->cleanInputs()->clean($a['optionTwo']));
                        $question->addAnswer($answer);
                    }

                    if(!empty($q['words']) && empty($q['answer_option_element'])) {
                        $answer = new Answer();
                        if(!empty($q['words'])) {
                            $answer->setIsRight(1);
                        }
                        $answer->setWorksheet($form->getData());
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

                        $tempImage = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\TempImage')->find($image);
                        if(!$tempImage) continue;

                        $questionImage = new QuestionImage();
                        $questionImage->setWorksheet($form->getData());
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
                $file->setType($file::TYPE_WORKSHEET);
                $file->setUser($user);
                $file->setWorksheet($form->getData());
                $file->setCategory($category);
                $this->getEntityManager()->persist($file);
                $this->getEntityManager()->flush();

                //add material to lesson plan
                $lessonPlanFile = new LessonPlanFile();
                $lessonPlanFile->setFile($file);
                $lessonPlanFile->setCategory($category);
                $lessonPlanFile->setUser($user);
                $lessonPlanFile->setType($file::TYPE_WORKSHEET);
                $this->getEntityManager()->persist($lessonPlanFile);
                $this->getEntityManager()->flush();

                $this->clearCache();
                $this->clearWorksheetCache($form->getData()->getId());

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
            $form->setHydrator(new DoctrineHydrator($this->getEntityManager(), 'AmaWorksheets\Entity\TempImage'))->setObject(new TempImage());
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
                        ->setTemplate("ama-worksheets/partial/image-thumb");

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

        $worksheet = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Worksheet')->findOneBy(array('id' => $file->getWorksheet(), 'user' => $user));
        if (!$worksheet) {
            return $this->redirect()->toRoute('error');
        }

        if ($request->isPost()) {
            $post = $request->getPost();
            $comment = $post->get('comment');
            $escaper = new Escaper('utf-8');
            $comment = $escaper->escapeHtml($comment);

            $file->setComment(nl2br($comment));
            $worksheet->setComment(nl2br($comment));
            $this->getEntityManager()->persist($file);
            $this->getEntityManager()->persist($worksheet);
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
        $worksheet = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Worksheet')->findOneBy(array('id' => $this->params('id'), 'user' => $user));
        if (!$worksheet) {
            return new JsonModel(array(
                'success' => false,
            ));
        }

        $question = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Question')->findOneBy(array('id' => $this->params('cid'), 'worksheet' => $worksheet));
        if (!$question) {
            return new JsonModel(array(
                'success' => false,
            ));
        }

        //Delete question images
        $images = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\QuestionImage')->findOneBy(array('question' => $question));
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

        $this->clearWorksheetCache($worksheet->getId());

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

        $question = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Question')->findOneBy(array('id' => $this->params('cid')));
        if (!$question) {
            return new JsonModel(array(
                'success' => false,
            ));
        }

        $user = $this->identity();
        $worksheet = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Worksheet')->findOneBy(array('id' => $question->getWorksheet(), 'user' => $user));
        if (!$worksheet) {
            return new JsonModel(array(
                'success' => false,
            ));
        }


        $file = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\QuestionImage')->findOneBy(array('id' => $this->params('id'), 'question' => $question));
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

        $this->clearWorksheetCache($worksheet->getId());

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
        $file = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\TempImage')->findOneBy(array('id' => $this->params('cid'), 'user' => $user));
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
        $worksheet = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Worksheet')->findOneBy(array('id' => $file->getWorksheet(), 'user' => $user));
        if (!$worksheet) {
            return $this->redirect()->toRoute('error');
        }

        $form = new AddForm($this->getEntityManager());
        $imageThumbForm  = new AddTempImageForm();
        $translator = $this->getServiceLocator()->get('translator');
        $htmlViewPart = new ViewModel();
        $htmlViewPart->setTerminal(true)
            ->setVariables(array(
                'form' => $form,
                'worksheet' => $worksheet,
                'file' => $file,
                //'category' => $category,
                'imageThumbForm' => $imageThumbForm
            ))
            ->setTemplate("ama-worksheets/manage/edit");

        $htmlOutput = $this->getServiceLocator()
            ->get('viewrenderer')
            ->render($htmlViewPart);

        return new JsonModel(array(
            'success' => true,
            'html' => $htmlOutput,
            'title' => $translator->translate("Edit Worksheet")
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

        $worksheet = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Worksheet')->findOneBy(array('id' => $this->params('id'), 'user' => $user));
        if (!$worksheet) {
            return new JsonModel(array(
                'success' => false
            ));
        }

        $translator = $this->getServiceLocator()->get('translator');
        $request = $this->getRequest();
        $message = '';
        $messageType = 'SUCCESS_SAVED';
        $success = true;
        $this->getConfig();

        if ($request->isPost()) {
            $form = new AddForm($this->getEntityManager());
            $form->setHydrator(new DoctrineHydrator($this->getEntityManager(), 'AmaWorksheets\Entity\Worksheet'))->setObject($worksheet);
            $form->bind($worksheet);

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
                        $question = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Question')->findOneBy(array('id' => $q['questionId'], 'worksheet' => $worksheet));
                        //delete all question answers
                        $answers = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Answer')->findBy(array('question' => $question));
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

                        $answer->setWorksheet($form->getData());
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
                        $answer->setWorksheet($form->getData());
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

                        $tempImage = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\TempImage')->find($image);
                        if(!$tempImage) continue;

                        $questionImage = new QuestionImage();
                        $questionImage->setWorksheet($form->getData());
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
                $this->clearWorksheetCache($worksheet->getId());

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
            'message_type' => $messageType,
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
        $worksheet = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Worksheet')->findOneBy(array('id' => $file->getWorksheet()));
        if (!$worksheet) {
            return $this->redirect()->toRoute('error');
        }

        $form = new AddForm($this->getEntityManager());
        $imageThumbForm  = new AddTempImageForm();
        $translator = $this->getServiceLocator()->get('translator');
        $htmlViewPart = new ViewModel();
        $htmlViewPart->setTerminal(true)
            ->setVariables(array(
                'form' => $form,
                'worksheet' => $worksheet,
                'file' => $file,
                'user' => $user,
                //'category' => $category,
                'imageThumbForm' => $imageThumbForm
            ))
            ->setTemplate("ama-worksheets/manage/no-edit");

        $htmlOutput = $this->getServiceLocator()
            ->get('viewrenderer')
            ->render($htmlViewPart);

        return new JsonModel(array(
            'success' => true,
            'html' => $htmlOutput,
            'title' => $translator->translate("Cannot edit worksheet")
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
        $worksheet = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Worksheet')->findWorksheetStatistics($this->params('cid'), $this->params('id'), $user->getId());
        if(!$worksheet) {
            return new JsonModel(array(
                'success' => false,
            ));
        }

        $worksheet['averagePoints'] = ceil($worksheet['totalRightPoints']/$worksheet['finishedUsersCount']);
        $worksheet['percentOfMax'] = ceil(($worksheet['averagePoints']/$worksheet['totalPoints'])*100);

        $worksheet['questions'] = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Worksheet')->findWorksheetStatisticsQuestions($worksheet['t_id'], $this->params('cid'), $this->params('id'), $user);
        foreach($worksheet['questions'] as $key => $question) {
            $worksheet['questions'][$key]['answers'] = array();
        }
        //var_dump($worksheet['questions']);

        $translator = $this->getServiceLocator()->get('translator');
        $htmlViewPart = new ViewModel();
        $htmlViewPart->setTerminal(true)
            ->setVariables(array(
                'worksheet' => $worksheet,
                'lessonPlanId' => $this->params('cid'),
                'fileClassId' => $this->params('id')
            ))
            ->setTemplate("ama-worksheets/manage/statistics");

        $htmlOutput = $this->getServiceLocator()
            ->get('viewrenderer')
            ->render($htmlViewPart);

        return new JsonModel(array(
            'success' => true,
            'html' => $htmlOutput,
            'title' => $translator->translate("Worksheet overview")
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
        $worksheet = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Worksheet')->findWorksheetStatistics($this->params('cid'), $this->params('id'), $user->getId());
        if(!$worksheet) {
            return new JsonModel(array(
                'success' => false,
                'html' => $translator->translate("No worksheet found!")
            ));
        }

        $answers = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Worksheet')->findWorksheetStatisticsQuestionAnswers($this->params('cid'), $this->params('id'), $user->getId(), $post->get('question_id'));
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
                'worksheet' => $worksheet,
                'lessonPlanId' => $this->params('cid'),
                'fileClassId' => $this->params('id')
            ))
            ->setTemplate("ama-worksheets/manage/statistics-answers");

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
        $worksheet = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Worksheet')->findWorksheetStatistics($this->params('cid'), $this->params('id'), $user->getId());
        if(!$worksheet) {
            return new JsonModel(array(
                'success' => false,
            ));
        }

        $worksheet['averagePoints'] = ceil($worksheet['totalRightPoints']/$worksheet['finishedUsersCount']);
        $worksheet['percentOfMax'] = ceil(($worksheet['averagePoints']/$worksheet['totalPoints'])*100);

        $worksheet['questions'] = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Worksheet')->findWorksheetStatisticsQuestions($worksheet['t_id'], $this->params('cid'), $this->params('id'), $user);
        $worksheet['answers'] = array();

        foreach($worksheet['questions'] as $key => $question) {
            $answers = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Worksheet')->findWorksheetStatisticsQuestionAnswers($this->params('cid'), $this->params('id'), $user->getId(), $question['q_id']);
            foreach($answers as $answer) {
                $answer['points'] = $question['q_points'];
                //$worksheet['answers'][$answer['s_firstName'] . ' ' . $answer['s_lastname']]['question_points'] = $question['q_points'];
                $worksheet['answers'][$answer['s_firstName'] . ' ' . $answer['s_lastname']]['student'] = $answer['u_id'];
                $worksheet['answers'][$answer['s_firstName'] . ' ' . $answer['s_lastname']]['answers'][] = $answer;
            }
        }

        $userWorksheetResult = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\UserWorksheetResult')->findOneBy(
            array(
                'sender' => $user,
                'worksheet' => $worksheet['t_id'],
                'lessonPlan' => $worksheet['lpf_id'],
                'fileClass' => $worksheet['fc_id']
            ));
        //var_dump($user->getId(), $worksheet['t_id'], $worksheet['lpf_id'], $worksheet['fc_id']);
        $translator = $this->getServiceLocator()->get('translator');
        $htmlViewPart = new ViewModel();
        $htmlViewPart->setTerminal(true)
            ->setVariables(array(
                'worksheet' => $worksheet,
                'lessonPlanId' => $this->params('cid'),
                'fileClassId' => $this->params('id'),
                'allreadySent' => $userWorksheetResult
            ))
            ->setTemplate("ama-worksheets/manage/valuation");

        $htmlOutput = $this->getServiceLocator()
            ->get('viewrenderer')
            ->render($htmlViewPart);

        return new JsonModel(array(
            'success' => true,
            'html' => $htmlOutput,
            'title' => $translator->translate("Worksheet valuation")
        ));
    }

    public function saveValuationAction()
    {
        $request = $this->getRequest();
        $translator = $this->getServiceLocator()->get('translator');
        $message = $translator->translate("No students have finished the worksheet!");
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
        $worksheet = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Worksheet')->findWorksheetStatistics($this->params('cid'), $this->params('id'), $user->getId());
        if(!$worksheet) {
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

            $userAnswer = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\UserWorksheetAnswer')->find($aid);
            if($userAnswer->getWorksheet()->getId()!=$worksheet['t_id']) continue;

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
            $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaWorksheetStatisticsQuestionAnswers' . $this->params('cid') . $this->params('id'). $lastQuestionId. $fileClassSender->getId());
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
        $message = $translator->translate("No students have finished the worksheet!");
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
        $worksheet = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Worksheet')->findWorksheetStatistics($this->params('cid'), $this->params('id'), $user->getId());
        if(!$worksheet) {
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
        $lessonPlanFile = $this->getEntityManager()->getRepository('AmaMaterials\Entity\LessonPlanFile')->find($worksheet['lpf_id']);
        $realWorksheet = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Worksheet')->find($worksheet['t_id']);

        $fileClassSender = $fileClass->getSender();
        $lastQuestionId = '';
        foreach($post['gradeComments'] as  $aid => $comment) {
            $comment  = $this->cleanInputs()->clean($comment);
            $grade    = $this->cleanInputs()->clean($post['grades'][$aid]);
            $aid      = $this->cleanInputs()->clean($aid);

            if(empty($aid)) continue;

            $userAnswer = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\UserWorksheetAnswer')->find($aid);
            if($userAnswer->getWorksheet()->getId()!=$worksheet['t_id']) continue;

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
                $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaWorksheetStatisticsQuestionAnswers' . $this->params('cid') . $this->params('id'). $lastQuestionId. $fileClassSender->getId());
            }
        }
        $this->getEntityManager()->flush();

        foreach($post['students'] as $userId) {
            $userId  = $this->cleanInputs()->clean($userId);
            if(empty($userId)) continue;

            $userWorksheetResult = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\UserWorksheetResult')->findOneBy(
                array(
                    'user' => $userId,
                    'sender' => $user,
                    'worksheet' => $worksheet['t_id'],
                    'lessonPlan' => $lessonPlanFile,
                    'fileClass' => $fileClass
                ));

            $success = true;
            $studentUser = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->find($userId);

            $message = $translator->translate('Teacher') . ' <strong>'. $user->getFirstName() .' ' . $user->getLastname() . '</strong> ' . $translator->translate("has sent you results for worksheet") .' ';
            $message .= '<a href="javascript:;" onclick="silmaring.openInModal(\''.$this->url()->fromRoute('solveWorksheets', array('action' => 'solved', 'id' => $lessonPlanFile->getId(), 'fid' => $fileClass->getId())).'\', \'modal-lg\');">' .$realWorksheet->getName() .'</a> ';

            $alert = new Alert();
            $alert->setUser($studentUser);
            $alert->setMessage($message);
            $this->getEntityManager()->persist($alert);

            if($userWorksheetResult) continue;

            $userWorksheetResult = new UserWorksheetResult();
            $userWorksheetResult->setUser($studentUser);
            $userWorksheetResult->setSender($user);
            $userWorksheetResult->setFileClass($fileClass);
            $userWorksheetResult->setWorksheet($realWorksheet);
            $userWorksheetResult->setLessonPlan($lessonPlanFile);
            $this->getEntityManager()->persist($userWorksheetResult);

        }

        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaSolveWorksheet' . $worksheet['lpf_id']);
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaSolveWorksheet' . $worksheet['fc_id']);

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
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaWorksheets' . $user->getId());
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaWorksheetsCount' . $user->getId());
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaLessonPlanCategoriesAndFiles' . $user->getId());

        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaLessonPlanFiles' . $user->getId());
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaLessonPlanFilesCount' . $user->getId());

        $categories = $this->getEntityManager()->getRepository('AmaCategories\Entity\Category')->findAllCategories();
        foreach($categories as $category){
            $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaWorksheets' . $category->getId() . $user->getId());
            $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaWorksheetsCount' . $category->getId() . $user->getId());

            $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaLessonPlanFiles' . $category->getId() . $user->getId());
            $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaLessonPlanFilesCount' . $category->getId() . $user->getId());
        }
    }

    public function clearWorksheetCache($worksheetId)
    {
        $questions = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Worksheet')->findQuestionsByWorksheetId($worksheetId);
        foreach($questions as $question) {
            $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaSolveWorksheetAnswers' . $question['q_id'] . $worksheetId);
            $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaSolveWorksheetRightAnswers' . $question['q_id'] . $worksheetId);
            $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaSolveWorksheetImages' . $question['q_id'] . $worksheetId);
        }
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaSolveWorksheet' . $worksheetId);
        $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaSolveWorksheetQuestions' . $worksheetId);
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