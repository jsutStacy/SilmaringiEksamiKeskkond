<?php

namespace AmaWorksheets\Controller;

use AmaMaterials\Entity\FileView;
use AmaWorksheets\Entity\UserWorksheetAnswer;
use AmaWorksheets\Entity\UserWorksheetAnsweredQuestion;
use AmaWorksheets\Form\SolveFilter;
use AmaWorksheets\Form\SolveForm;
use Zend\Mvc\Controller\AbstractActionController;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class SolveController extends AbstractActionController
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
        $request = $this->getRequest();
        if (!$request->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        if ($this->params('id')==0) {
            return $this->redirect()->toRoute('error');
        }

        $id = $this->params('id');
        $asLpf = true;
        if($this->params('fid')>0) {
            $asLpf = false;
            $id = $this->params('fid');
        }
        $translator = $this->getServiceLocator()->get('translator');
        $user = $this->identity();
        $worksheet = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Worksheet')->findWorksheetById($id, $asLpf);
        if(!$worksheet) {
            return $this->redirect()->toRoute('error');
        }

        $canAccess = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Worksheet')->checkIfUserCanAccessWorksheet($user, $id, $asLpf);
        if(!$canAccess) {
            return $this->redirect()->toRoute('error');
        }

        $allreadyAnswered = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\UserWorksheetAnsweredQuestion')->findOneBy(array('worksheet' => $worksheet['t_id'], 'user' => $user, 'lessonPlan' => $worksheet['lpf_id'], 'fileClass' => $worksheet['fc_id']));
        if($allreadyAnswered) {
            return new JsonModel(array(
                'success' => true,
                'html' => $translator->translate("Worksheet has been allready solved by you.") .' ' . $translator->translate("You can view it") .' <a href="javascript:;" onclick="silmaring.openInModal(\''.$this->url()->fromRoute('solveWorksheets', array('action' => 'solved', 'id' => $worksheet['lpf_id'], 'fid' => $worksheet['fc_id'])).'\', \'modal-lg\');">' . $translator->translate("here") .'</a>',
                'title' => $translator->translate("Solved Worksheet")
            ));
        }

        $questions = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Worksheet')->findQuestionsByWorksheetId($worksheet['t_id']);
        foreach($questions as $key =>  $question)
        {
            $answers = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Worksheet')->findAnswersByQuestionId($question['q_id'], $worksheet['t_id']);
            $questions[$key]['answers'] = $answers;

            $images = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Worksheet')->findImagesByQuestionId($question['q_id'], $worksheet['t_id']);
            $questions[$key]['images'] = $images;
        }

        $form = new SolveForm($this->getEntityManager());

        $htmlViewPart = new ViewModel();
        $htmlViewPart->setTerminal(true)
            ->setVariables(array(
                'worksheet' => $worksheet,
                'questions' => $questions,
                'user' => $user,
                'form' => $form
            ))
            ->setTemplate("ama-worksheets/solve/index");

        $htmlOutput = $this->getServiceLocator()
            ->get('viewrenderer')
            ->render($htmlViewPart);

        return new JsonModel(array(
            'success' => true,
            'html' => $htmlOutput,
            'title' => $translator->translate("Solve Worksheet")
        ));
    }

    public function readyAction()
    {
        $request = $this->getRequest();
        if (!$request->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        if ($this->params('id')==0) {
            return $this->redirect()->toRoute('error');
        }

        $user = $this->identity();
        $worksheetExists = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Worksheet')->findWorksheetById($this->params('id'), false);
        if(!$worksheetExists) {
            return $this->redirect()->toRoute('error');
        }

        $canAccess = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Worksheet')->checkIfUserCanAccessWorksheet($user, $this->params('id'), false);
        if(!$canAccess) {
            return $this->redirect()->toRoute('error');
        }

        $allreadyAnswered = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\UserWorksheetAnsweredQuestion')->findOneBy(array('worksheet' => $worksheetExists['t_id'], 'user' => $user, 'lessonPlan' => $worksheetExists['lpf_id'], 'fileClass' => $worksheetExists['fc_id']));
        if($allreadyAnswered) {
            return $this->redirect()->toRoute('error');
        }

        $translator = $this->getServiceLocator()->get('translator');
        $message = '';
        $success = true;

        if ($request->isPost()) {
            $form = new SolveForm($this->getEntityManager());
            $worksheetAnswer = new UserWorksheetAnswer();
            $form->setHydrator(new DoctrineHydrator($this->getEntityManager(), 'AmaWorksheets\Entity\UserWorksheetAnswer'))->setObject($worksheetAnswer);
            $form->bind($worksheetAnswer);

            $post = array_merge_recursive(
                $request->getPost()->toArray()
            );

            $form->setInputFilter(new SolveFilter($this->getServiceLocator()));
            $form->setData($post);
            //error_reporting(E_ALL);
            //ini_set("display_errors", 1);
            if ( $form->isValid() ) {

                $lessonPlan = $this->getEntityManager()->getRepository('AmaMaterials\Entity\LessonPlanFile')->find($worksheetExists['lpf_id']);
                $fileClass  = $this->getEntityManager()->getRepository('AmaMaterials\Entity\FileClass')->find($worksheetExists['fc_id']);
                $fileClassSender = $fileClass->getSender();

                foreach($post['user_question_element'] as $questionId => $answers) {
                    $question = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Question')->find($questionId);
                    $worksheet = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Worksheet')->find($worksheetExists['t_id']);

                    //if($question->getAnswerType() == 1) {
                        $this->saveAnswerWithWords($user, $worksheet, $lessonPlan, $fileClass, $question, $answers['user_answer_element']['answerText'][0]);
                    /*}
                    else if($question->getAnswerType() == 3)
                    {
                        $this->saveAnswerWithRange($user, $worksheet, $lessonPlan, $fileClass, $question, $answers['user_answer_element']['answerText'][0]);
                    }
                    else {
                        $this->saveAnswerWithOptions($user, $worksheet, $lessonPlan, $fileClass, $question, $answers['user_answer_element']['answer']);
                    }*/

                    //clean cache for answers in statistics
                    $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaWorksheetStatisticsQuestionAnswers' . $worksheetExists['f_id'] . $worksheetExists['fc_id']. $question->getId(). $fileClassSender->getId());

                }

                $fileView = new FileView();
                $fileView->setFile($lessonPlan);
                $fileView->setViewer($user);
                $fileView->setFileClass($fileClass);
                $this->getEntityManager()->persist($fileView);
                $this->getEntityManager()->flush();

                //clear cache
                $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaFilesSentToClasses' . $worksheetExists['f_id'] . $fileClassSender->getId());
                $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaWorksheetStatistics' . $worksheetExists['f_id'] . $worksheetExists['fc_id'] . $fileClassSender->getId());
                $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaWorksheetStatisticsQuestions' . $worksheetExists['f_id'] . $worksheetExists['fc_id'] . $fileClassSender->getId());
                $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('userClassesFiles' . $user->getId());
                $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('userClassesFilesWithCategory' . $user->getId());
                $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('userClassesFilesWithCategory' . $lessonPlan->getCategory()->getId(). $user->getId());


                $message = $translator->translate('Successfully answered!');
            }
            else {
                $success = false;
                $message = $this->formatMessage()->doFormat($form->getMessages());
            }
        }


        return new JsonModel(array(
            'success' => $success,
            'message' => $message,
        ));
    }

    public function saveAnswerWithOptions($user, $worksheet, $lessonPlan, $fileClass, $question, $selectedAnswers)
    {
        $rightAnswers = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Worksheet')->findRightAnswersByQuestionId($question->getId(),$worksheet->getId());

        $isRight = 1;
        foreach($rightAnswers as $answer) {
            if(!in_array($answer,$selectedAnswers)) {
                $isRight = 0;
                break;
            }
        }

        //var_dump($isRight . ' ' . $question->getid());
        $worksheetAnsweredQuestion = new UserWorksheetAnsweredQuestion();
        $worksheetAnsweredQuestion->setWorksheet($worksheet);
        $worksheetAnsweredQuestion->setQuestion($question);
        $worksheetAnsweredQuestion->setIsRight($isRight);
        $worksheetAnsweredQuestion->setUser($user);
        $worksheetAnsweredQuestion->setPoints($question->getPoints());
        $worksheetAnsweredQuestion->setLessonPlan($lessonPlan);
        $worksheetAnsweredQuestion->setFileClass($fileClass);

        $this->getEntityManager()->persist($worksheetAnsweredQuestion);
        $this->getEntityManager()->flush();

        if(!is_array($selectedAnswers)) {
            $oldSelectedAnswers = $selectedAnswers;
            $selectedAnswers = array();
            $selectedAnswers[] = $oldSelectedAnswers;
        }

        foreach($selectedAnswers as $answer) {
            $answer = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Answer')->find($answer);

            $worksheetAnswer = new UserWorksheetAnswer();
            $worksheetAnswer->setWorksheet($worksheet);
            $worksheetAnswer->setQuestion($question);
            $worksheetAnswer->setAnswer($answer);
            $worksheetAnswer->setAnsweredQuestion($worksheetAnsweredQuestion);
            $worksheetAnswer->setUser($user);
            $worksheetAnswer->setIsRight($answer->getIsRight());
            $worksheetAnswer->setLessonPlan($lessonPlan);
            $worksheetAnswer->setFileClass($fileClass);
            if($isRight==1)
            $worksheetAnswer->setPoints($question->getPoints());

            $this->getEntityManager()->persist($worksheetAnswer);
            $this->getEntityManager()->flush();
        }
    }

    public function saveAnswerWithWords($user, $worksheet, $lessonPlan, $fileClass, $question, $writtenAnswer)
    {

        $rightAnswer = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Answer')->findOneBy(array('question' => $question, 'worksheet' => $worksheet->getId()));
        $isRight = 0;
        if($rightAnswer) {
            if (preg_match("/".trim($rightAnswer->getWords())."/i", $writtenAnswer)) {
                $isRight = 1;
            }
        }

        //var_dump($isRight . ' ' . $question->getid());
        $worksheetAnsweredQuestion = new UserWorksheetAnsweredQuestion();
        $worksheetAnsweredQuestion->setWorksheet($worksheet);
        $worksheetAnsweredQuestion->setQuestion($question);
        $worksheetAnsweredQuestion->setIsRight($isRight);
        $worksheetAnsweredQuestion->setUser($user);
        $worksheetAnsweredQuestion->setPoints($question->getPoints());
        $worksheetAnsweredQuestion->setLessonPlan($lessonPlan);
        $worksheetAnsweredQuestion->setFileClass($fileClass);


        $this->getEntityManager()->persist($worksheetAnsweredQuestion);
        $this->getEntityManager()->flush();

        $worksheetAnswer = new UserWorksheetAnswer();
        $worksheetAnswer->setWorksheet($worksheet);
        $worksheetAnswer->setQuestion($question);
        $worksheetAnswer->setAnswerText($this->cleanInputs()->clean($writtenAnswer));
        $worksheetAnswer->setAnsweredQuestion($worksheetAnsweredQuestion);
        $worksheetAnswer->setUser($user);
        $worksheetAnswer->setIsRight($isRight);
        $worksheetAnswer->setLessonPlan($lessonPlan);
        $worksheetAnswer->setFileClass($fileClass);
        if($isRight==1)
        $worksheetAnswer->setPoints($question->getPoints());

        $this->getEntityManager()->persist($worksheetAnswer);
        $this->getEntityManager()->flush();
    }

    public function saveAnswerWithRange($user, $worksheet, $lessonPlan, $fileClass, $question, $writtenAnswer)
    {
        $rightAnswers = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Answer')->findBy(array('question' => $question, 'worksheet' => $worksheet, 'isRight' => 1));

        $isRight = 1;
        foreach($rightAnswers as $answer) {
            if(!$answer->getOptionTwo()) {
                if (!preg_match("/".trim($answer->getOption())."/i", $writtenAnswer)) {
                    $isRight = 0;
                    break;
                }
            }
            else {
                $isRight = 0;
                if((int)$answer->getOption()>=(int)$writtenAnswer && (int)$answer->getOptionTwo()<=(int)$writtenAnswer) {
                    $isRight = 1;
                }
            }
        }

        //var_dump($isRight . ' ' . $question->getid());
        $worksheetAnsweredQuestion = new UserWorksheetAnsweredQuestion();
        $worksheetAnsweredQuestion->setWorksheet($worksheet);
        $worksheetAnsweredQuestion->setQuestion($question);
        $worksheetAnsweredQuestion->setIsRight($isRight);
        $worksheetAnsweredQuestion->setUser($user);
        $worksheetAnsweredQuestion->setPoints($question->getPoints());
        $worksheetAnsweredQuestion->setLessonPlan($lessonPlan);
        $worksheetAnsweredQuestion->setFileClass($fileClass);

        $this->getEntityManager()->persist($worksheetAnsweredQuestion);
        $this->getEntityManager()->flush();

        $worksheetAnswer = new UserWorksheetAnswer();
        $worksheetAnswer->setWorksheet($worksheet);
        $worksheetAnswer->setQuestion($question);
        $worksheetAnswer->setAnswerText($this->cleanInputs()->clean($writtenAnswer));
        $worksheetAnswer->setAnsweredQuestion($worksheetAnsweredQuestion);
        $worksheetAnswer->setUser($user);
        $worksheetAnswer->setIsRight($isRight);
        $worksheetAnswer->setLessonPlan($lessonPlan);
        $worksheetAnswer->setFileClass($fileClass);
        if($isRight==1)
        $worksheetAnswer->setPoints($question->getPoints());


        $this->getEntityManager()->persist($worksheetAnswer);
        $this->getEntityManager()->flush();
    }

    public function solvedAction()
    {
        $request = $this->getRequest();
        if (!$request->isXmlHttpRequest()) {
            return $this->redirect()->toRoute('error');
        }

        if ($this->params('id')==0) {
            return $this->redirect()->toRoute('error');
        }

        $id = $this->params('id');
        $asLpf = true;
        if($this->params('fid')>0) {
            $asLpf = false;
            $id = $this->params('fid');
        }

        $user = $this->identity();
        $worksheet = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Worksheet')->findWorksheetById($id, $asLpf, true);
        if(!$worksheet) {
            return $this->redirect()->toRoute('error');
        }

        $canAccess = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Worksheet')->checkIfUserCanAccessWorksheet($user, $id, $asLpf);
        if(!$canAccess) {
            return $this->redirect()->toRoute('error');
        }
        $worksheet['percent'] = ceil(($worksheet['totalRightPoints']/$worksheet['totalPoints'])*100);

        $questions = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Worksheet')->findQuestionsByWorksheetId($worksheet['t_id']);
        foreach($questions as $key =>  $question)
        {
            $answers = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Worksheet')->findAnswersByQuestionId($question['q_id'], $worksheet['t_id']);
            $questions[$key]['answers'] = $answers;

            $images = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\Worksheet')->findImagesByQuestionId($question['q_id'], $worksheet['t_id']);
            $questions[$key]['images'] = $images;

            $userAnswer = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\UserWorksheetAnswer')->findOneBy(array("worksheet" => $worksheet['t_id'], 'question' => $question['q_id'], 'user' => $user, 'lessonPlan' => $worksheet['lpf_id'], 'fileClass' => $worksheet['fc_id']));
            $questions[$key]['userAnswer'] = $userAnswer;
        }

        $userWorksheetResult = $this->getEntityManager()->getRepository('AmaWorksheets\Entity\UserWorksheetResult')->findOneBy(
            array(
                'user' => $user,
                'worksheet' => $worksheet['t_id'],
                'lessonPlan' => $worksheet['lpf_id'],
                'fileClass' => $worksheet['fc_id']
            ));

        $form = new SolveForm($this->getEntityManager());
        $translator = $this->getServiceLocator()->get('translator');
        $htmlViewPart = new ViewModel();
        $htmlViewPart->setTerminal(true)
            ->setVariables(array(
                'worksheet' => $worksheet,
                'questions' => $questions,
                'user' => $user,
                'form' => $form,
                'worksheetResults' => $userWorksheetResult,
            ))
            ->setTemplate("ama-worksheets/solve/solved");

        $htmlOutput = $this->getServiceLocator()
            ->get('viewrenderer')
            ->render($htmlViewPart);

        return new JsonModel(array(
            'success' => true,
            'html' => $htmlOutput,
            'title' => $translator->translate("Solve Worksheet")
        ));
    }


    public function getEntityManager()
    {
        if (!$this->em) {
            $this->em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        }
        return $this->em;
    }

}

