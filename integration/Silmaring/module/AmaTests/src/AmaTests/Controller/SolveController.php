<?php

namespace AmaTests\Controller;

use AmaMaterials\Entity\FileView;
use AmaTests\Entity\UserTestAnswer;
use AmaTests\Entity\UserTestAnsweredQuestion;
use AmaTests\Form\SolveFilter;
use AmaTests\Form\SolveForm;
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
        $test = $this->getEntityManager()->getRepository('AmaTests\Entity\Test')->findTestById($id, $asLpf);
        if(!$test) {
            return $this->redirect()->toRoute('error');
        }

        $canAccess = $this->getEntityManager()->getRepository('AmaTests\Entity\Test')->checkIfUserCanAccessTest($user, $id, $asLpf);
        if(!$canAccess) {
            return $this->redirect()->toRoute('error');
        }

        $allreadyAnswered = $this->getEntityManager()->getRepository('AmaTests\Entity\UserTestAnsweredQuestion')->findOneBy(array('test' => $test['t_id'], 'user' => $user, 'lessonPlan' => $test['lpf_id'], 'fileClass' => $test['fc_id']));
        if($allreadyAnswered) {
            return new JsonModel(array(
                'success' => true,
                'html' => $translator->translate("Test has been allready solved by you.") .' ' . $translator->translate("You can view it") .' <a href="javascript:;" onclick="silmaring.openInModal(\''.$this->url()->fromRoute('solveTests', array('action' => 'solved', 'id' => $test['lpf_id'], 'fid' => $test['fc_id'])).'\', \'modal-lg\');">' . $translator->translate("here") .'</a>',
                'title' => $translator->translate("Solved Test")
            ));
        }

        $questions = $this->getEntityManager()->getRepository('AmaTests\Entity\Test')->findQuestionsByTestId($test['t_id']);
        foreach($questions as $key =>  $question)
        {
            $answers = $this->getEntityManager()->getRepository('AmaTests\Entity\Test')->findAnswersByQuestionId($question['q_id'], $test['t_id']);
            $questions[$key]['answers'] = $answers;

            $images = $this->getEntityManager()->getRepository('AmaTests\Entity\Test')->findImagesByQuestionId($question['q_id'], $test['t_id']);
            $questions[$key]['images'] = $images;
        }

        $form = new SolveForm($this->getEntityManager());

        $htmlViewPart = new ViewModel();
        $htmlViewPart->setTerminal(true)
            ->setVariables(array(
                'test' => $test,
                'questions' => $questions,
                'user' => $user,
                'form' => $form
            ))
            ->setTemplate("ama-tests/solve/index");

        $htmlOutput = $this->getServiceLocator()
            ->get('viewrenderer')
            ->render($htmlViewPart);

        return new JsonModel(array(
            'success' => true,
            'html' => $htmlOutput,
            'title' => $translator->translate("Solve Test")
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
        $testExists = $this->getEntityManager()->getRepository('AmaTests\Entity\Test')->findTestById($this->params('id'), false);
        if(!$testExists) {
            return $this->redirect()->toRoute('error');
        }

        $canAccess = $this->getEntityManager()->getRepository('AmaTests\Entity\Test')->checkIfUserCanAccessTest($user, $this->params('id'), false);
        if(!$canAccess) {
            return $this->redirect()->toRoute('error');
        }

        $allreadyAnswered = $this->getEntityManager()->getRepository('AmaTests\Entity\UserTestAnsweredQuestion')->findOneBy(array('test' => $testExists['t_id'], 'user' => $user, 'lessonPlan' => $testExists['lpf_id'], 'fileClass' => $testExists['fc_id']));
        if($allreadyAnswered) {
            return $this->redirect()->toRoute('error');
        }

        $translator = $this->getServiceLocator()->get('translator');
        $message = '';
        $success = true;

        if ($request->isPost()) {
            $form = new SolveForm($this->getEntityManager());
            $testAnswer = new UserTestAnswer();
            $form->setHydrator(new DoctrineHydrator($this->getEntityManager(), 'AmaTests\Entity\UserTestAnswer'))->setObject($testAnswer);
            $form->bind($testAnswer);

            $post = array_merge_recursive(
                $request->getPost()->toArray()
            );

            $form->setInputFilter(new SolveFilter($this->getServiceLocator()));
            $form->setData($post);
            //error_reporting(E_ALL);
            //ini_set("display_errors", 1);
            if ( $form->isValid() ) {

                $lessonPlan = $this->getEntityManager()->getRepository('AmaMaterials\Entity\LessonPlanFile')->find($testExists['lpf_id']);
                $fileClass  = $this->getEntityManager()->getRepository('AmaMaterials\Entity\FileClass')->find($testExists['fc_id']);
                $fileClassSender = $fileClass->getSender();

                foreach($post['user_question_element'] as $questionId => $answers) {
                    $question = $this->getEntityManager()->getRepository('AmaTests\Entity\Question')->find($questionId);
                    $test = $this->getEntityManager()->getRepository('AmaTests\Entity\Test')->find($testExists['t_id']);

                    if($question->getAnswerType() == 1) {
                        $this->saveAnswerWithWords($user, $test, $lessonPlan, $fileClass, $question, $answers['user_answer_element']['answerText'][0]);
                    }
                    else if($question->getAnswerType() == 3)
                    {
                        $this->saveAnswerWithRange($user, $test, $lessonPlan, $fileClass, $question, $answers['user_answer_element']['answerText'][0]);
                    }
                    else {
                        $this->saveAnswerWithOptions($user, $test, $lessonPlan, $fileClass, $question, $answers['user_answer_element']['answer']);
                    }

                    //clean cache for answers in statistics
                    $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaTestStatisticsQuestionAnswers' . $testExists['f_id'] . $testExists['fc_id']. $question->getId(). $fileClassSender->getId());

                }

                $fileView = new FileView();
                $fileView->setFile($lessonPlan);
                $fileView->setViewer($user);
                $fileView->setFileClass($fileClass);
                $this->getEntityManager()->persist($fileView);
                $this->getEntityManager()->flush();

                //clear cache
                $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaFilesSentToClasses' . $testExists['f_id'] . $fileClassSender->getId());
                $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaTestStatistics' . $testExists['f_id'] . $testExists['fc_id'] . $fileClassSender->getId());
                $this->getEntityManager()->getConfiguration()->getResultCacheImpl()->delete('AmaTestStatisticsQuestions' . $testExists['f_id'] . $testExists['fc_id'] . $fileClassSender->getId());
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

    public function saveAnswerWithOptions($user, $test, $lessonPlan, $fileClass, $question, $selectedAnswers)
    {
        $rightAnswers = $this->getEntityManager()->getRepository('AmaTests\Entity\Test')->findRightAnswersByQuestionId($question->getId(),$test->getId());

        $isRight = 1;
        foreach($rightAnswers as $answer) {
            if(!in_array($answer,$selectedAnswers)) {
                $isRight = 0;
                break;
            }
        }

        //var_dump($isRight . ' ' . $question->getid());
        $testAnsweredQuestion = new UserTestAnsweredQuestion();
        $testAnsweredQuestion->setTest($test);
        $testAnsweredQuestion->setQuestion($question);
        $testAnsweredQuestion->setIsRight($isRight);
        $testAnsweredQuestion->setUser($user);
        $testAnsweredQuestion->setPoints($question->getPoints());
        $testAnsweredQuestion->setLessonPlan($lessonPlan);
        $testAnsweredQuestion->setFileClass($fileClass);

        $this->getEntityManager()->persist($testAnsweredQuestion);
        $this->getEntityManager()->flush();

        if(!is_array($selectedAnswers)) {
            $oldSelectedAnswers = $selectedAnswers;
            $selectedAnswers = array();
            $selectedAnswers[] = $oldSelectedAnswers;
        }

        foreach($selectedAnswers as $answer) {
            $answer = $this->getEntityManager()->getRepository('AmaTests\Entity\Answer')->find($answer);

            $testAnswer = new UserTestAnswer();
            $testAnswer->setTest($test);
            $testAnswer->setQuestion($question);
            $testAnswer->setAnswer($answer);
            $testAnswer->setAnsweredQuestion($testAnsweredQuestion);
            $testAnswer->setUser($user);
            $testAnswer->setIsRight($answer->getIsRight());
            $testAnswer->setLessonPlan($lessonPlan);
            $testAnswer->setFileClass($fileClass);
            if($isRight==1)
            $testAnswer->setPoints($question->getPoints());

            $this->getEntityManager()->persist($testAnswer);
            $this->getEntityManager()->flush();
        }
    }

    public function saveAnswerWithWords($user, $test, $lessonPlan, $fileClass, $question, $writtenAnswer)
    {

        $rightAnswer = $this->getEntityManager()->getRepository('AmaTests\Entity\Answer')->findOneBy(array('question' => $question, 'test' => $test->getId()));

        if($rightAnswer->getMustContainWords()==1) {

            $words = explode(",", $rightAnswer->getWords());
            $isRight = 1;
            foreach($words as $word) {
                if (!preg_match("/".trim($word)."/i", $writtenAnswer)) {
                    $isRight = 0;
                    break;
                }
            }
        }
        else {
            $isRight = 0;
            if (preg_match("/".trim($rightAnswer->getWords())."/i", $writtenAnswer)) {
                $isRight = 1;
            }
        }

        //var_dump($isRight . ' ' . $question->getid());
        $testAnsweredQuestion = new UserTestAnsweredQuestion();
        $testAnsweredQuestion->setTest($test);
        $testAnsweredQuestion->setQuestion($question);
        $testAnsweredQuestion->setIsRight($isRight);
        $testAnsweredQuestion->setUser($user);
        $testAnsweredQuestion->setPoints($question->getPoints());
        $testAnsweredQuestion->setLessonPlan($lessonPlan);
        $testAnsweredQuestion->setFileClass($fileClass);


        $this->getEntityManager()->persist($testAnsweredQuestion);
        $this->getEntityManager()->flush();

        $testAnswer = new UserTestAnswer();
        $testAnswer->setTest($test);
        $testAnswer->setQuestion($question);
        $testAnswer->setAnswerText($this->cleanInputs()->clean($writtenAnswer));
        $testAnswer->setAnsweredQuestion($testAnsweredQuestion);
        $testAnswer->setUser($user);
        $testAnswer->setIsRight($isRight);
        $testAnswer->setLessonPlan($lessonPlan);
        $testAnswer->setFileClass($fileClass);
        if($isRight==1)
        $testAnswer->setPoints($question->getPoints());

        $this->getEntityManager()->persist($testAnswer);
        $this->getEntityManager()->flush();
    }

    public function saveAnswerWithRange($user, $test, $lessonPlan, $fileClass, $question, $writtenAnswer)
    {
        $writtenAnswer = trim($writtenAnswer);
        $rightAnswers = $this->getEntityManager()->getRepository('AmaTests\Entity\Answer')->findBy(array('question' => $question, 'test' => $test, 'isRight' => 1));

        $isRight = 1;
        foreach($rightAnswers as $answer) {
            $optionTwo = trim($answer->getOptionTwo());
            $option = trim($answer->getOption());
            if(empty($optionTwo)) {
                if (!preg_match("/".trim($option)."/i", $writtenAnswer)) {
                    $isRight = 0;
                    break;
                }
            }
            else {
                $isRight = 0;
                if((int)$option>=(int)$writtenAnswer && (int)$optionTwo<=(int)$writtenAnswer) {
                    $isRight = 1;
                }
            }
        }

        //var_dump($isRight . ' ' . $question->getid());
        $testAnsweredQuestion = new UserTestAnsweredQuestion();
        $testAnsweredQuestion->setTest($test);
        $testAnsweredQuestion->setQuestion($question);
        $testAnsweredQuestion->setIsRight($isRight);
        $testAnsweredQuestion->setUser($user);
        $testAnsweredQuestion->setPoints($question->getPoints());
        $testAnsweredQuestion->setLessonPlan($lessonPlan);
        $testAnsweredQuestion->setFileClass($fileClass);

        $this->getEntityManager()->persist($testAnsweredQuestion);
        $this->getEntityManager()->flush();

        $testAnswer = new UserTestAnswer();
        $testAnswer->setTest($test);
        $testAnswer->setQuestion($question);
        $testAnswer->setAnswerText($this->cleanInputs()->clean($writtenAnswer));
        $testAnswer->setAnsweredQuestion($testAnsweredQuestion);
        $testAnswer->setUser($user);
        $testAnswer->setIsRight($isRight);
        $testAnswer->setLessonPlan($lessonPlan);
        $testAnswer->setFileClass($fileClass);
        if($isRight==1)
        $testAnswer->setPoints($question->getPoints());


        $this->getEntityManager()->persist($testAnswer);
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
        $test = $this->getEntityManager()->getRepository('AmaTests\Entity\Test')->findTestById($id, $asLpf, true);
        if(!$test) {
            return $this->redirect()->toRoute('error');
        }

        $canAccess = $this->getEntityManager()->getRepository('AmaTests\Entity\Test')->checkIfUserCanAccessTest($user, $id, $asLpf);
        if(!$canAccess) {
            return $this->redirect()->toRoute('error');
        }
        $test['percent'] = ceil(($test['totalRightPoints']/$test['totalPoints'])*100);

        $questions = $this->getEntityManager()->getRepository('AmaTests\Entity\Test')->findQuestionsByTestId($test['t_id']);
        foreach($questions as $key =>  $question)
        {
            $answers = $this->getEntityManager()->getRepository('AmaTests\Entity\Test')->findAnswersByQuestionId($question['q_id'], $test['t_id']);
            $questions[$key]['answers'] = $answers;

            $images = $this->getEntityManager()->getRepository('AmaTests\Entity\Test')->findImagesByQuestionId($question['q_id'], $test['t_id']);
            $questions[$key]['images'] = $images;

            $userAnswers = $this->getEntityManager()->getRepository('AmaTests\Entity\UserTestAnswer')->findBy(array("test" => $test['t_id'], 'question' => $question['q_id'], 'user' => $user, 'lessonPlan' => $test['lpf_id'], 'fileClass' => $test['fc_id']));
            if(count($userAnswers)>1) {
                $userAnswersArray = array();
                foreach($userAnswers as $userAnswer) {
                    if(!$userAnswer->getAnswer()) continue;
                    $userAnswersArray[$userAnswer->getAnswer()->getId()] = $userAnswer->getAnswer()->getId();
                }
                $questions[$key]['userAnswer'] = $userAnswersArray;
            }
            else {
                $questions[$key]['userAnswer'] = $userAnswers[0];
            }
        }

        $userTestResult = $this->getEntityManager()->getRepository('AmaTests\Entity\UserTestResult')->findOneBy(
            array(
                'user' => $user,
                'test' => $test['t_id'],
                'lessonPlan' => $test['lpf_id'],
                'fileClass' => $test['fc_id']
            ));

        $form = new SolveForm($this->getEntityManager());
        $translator = $this->getServiceLocator()->get('translator');
        $htmlViewPart = new ViewModel();
        $htmlViewPart->setTerminal(true)
            ->setVariables(array(
                'test' => $test,
                'questions' => $questions,
                'user' => $user,
                'form' => $form,
                'testResults' => $userTestResult,
            ))
            ->setTemplate("ama-tests/solve/solved");

        $htmlOutput = $this->getServiceLocator()
            ->get('viewrenderer')
            ->render($htmlViewPart);

        return new JsonModel(array(
            'success' => true,
            'html' => $htmlOutput,
            'title' => $translator->translate("Solve Test")
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

