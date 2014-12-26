<?php

namespace AmaTests\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class UserTestAnswer
 * @package AmaTests\Entity
 * @ORM\Entity
 * @ORM\Table(name="test_user_answers")
 */
class UserTestAnswer
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="AmaTests\Entity\Test")
     * @ORM\JoinColumn(name="test_id", referencedColumnName="id", nullable=false)
     */
    protected $test;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="AmaMaterials\Entity\LessonPlanFile")
     * @ORM\JoinColumn(name="lesson_plan_id", referencedColumnName="id", nullable=false)
     */
    protected $lessonPlan;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="AmaMaterials\Entity\FileClass", inversedBy="userTestAnswers")
     * @ORM\JoinColumn(name="file_class_id", referencedColumnName="id", nullable=false)
     */
    protected $fileClass;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="AmaTests\Entity\Question")
     * @ORM\JoinColumn(name="question_id", referencedColumnName="id", nullable=false)
     */
    protected $question;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="AmaTests\Entity\UserTestAnsweredQuestion", inversedBy="answeredQuestions")
     * @ORM\JoinColumn(name="answered_question_id", referencedColumnName="id", nullable=false)
     */
    protected $answeredQuestion;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="AmaTests\Entity\Answer")
     * @ORM\JoinColumn(name="answer_id", referencedColumnName="id")
     */
    protected $answer;

    /**
     * @ORM\ManyToOne(targetEntity="AmaUsers\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     **/
    protected $user;

    /**
     * @var string
     * @ORM\Column(name="answer_text", type="text", nullable=true)
     */
    protected $answerText;

    /**
     * @var string
     * @ORM\Column(name="teacher_comment", type="text", nullable=true)
     */
    protected $comment;

    /**
     * @var int
     * @ORM\Column(name="teacher_grade", type="smallint", length=1, options={"default" = 0})
     */
    protected $grade = 0;

    /**
     * @var integer
     * @ORM\Column(name="points", type="integer", length=11, options={"default" = 0})
     */
    protected $points = 0;

    /**
     * @var int
     * @ORM\Column(name="is_right", type="smallint", length=1, options={"default" = 0})
     */
    protected $isRight = 0;

    /**
     * @var datetime
     * @ORM\Column(name="date_added", type="datetime")
     */
    protected $dateAdded;


    public function __construct()
    {
        $this->dateAdded = new \DateTime();
    }

    /**
     * @param string $answer
     */
    public function setAnswer($answer)
    {
        $this->answer = $answer;
    }

    /**
     * @return string
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * @param \AmaTests\Entity\datetime $dateAdded
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;
    }

    /**
     * @return \AmaTests\Entity\datetime
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $question
     */
    public function setQuestion($question)
    {
        $this->question = $question;
    }

    /**
     * @return string
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @param string $test
     */
    public function setTest($test)
    {
        $this->test = $test;
    }

    /**
     * @return string
     */
    public function getTest()
    {
        return $this->test;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $answerText
     */
    public function setAnswerText($answerText)
    {
        $this->answerText = $answerText;
    }

    /**
     * @return string
     */
    public function getAnswerText()
    {
        return $this->answerText;
    }

    /**
     * @param string $answeredQuestion
     */
    public function setAnsweredQuestion($answeredQuestion)
    {
        $this->answeredQuestion = $answeredQuestion;
    }

    /**
     * @return string
     */
    public function getAnsweredQuestion()
    {
        return $this->answeredQuestion;
    }

    /**
     * @param int $isRight
     */
    public function setIsRight($isRight)
    {
        $this->isRight = $isRight;
    }

    /**
     * @return int
     */
    public function getIsRight()
    {
        return $this->isRight;
    }

    /**
     * @param string $fileClass
     */
    public function setFileClass($fileClass)
    {
        $this->fileClass = $fileClass;
    }

    /**
     * @return string
     */
    public function getFileClass()
    {
        return $this->fileClass;
    }

    /**
     * @param string $lessonPlan
     */
    public function setLessonPlan($lessonPlan)
    {
        $this->lessonPlan = $lessonPlan;
    }

    /**
     * @return string
     */
    public function getLessonPlan()
    {
        return $this->lessonPlan;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param int $grade
     */
    public function setGrade($grade)
    {
        $this->grade = $grade;
    }

    /**
     * @return int
     */
    public function getGrade()
    {
        return $this->grade;
    }

    /**
     * @param int $points
     */
    public function setPoints($points)
    {
        $this->points = $points;
    }

    /**
     * @return int
     */
    public function getPoints()
    {
        return $this->points;
    }



}