<?php

namespace AmaWorksheets\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Question
 * @package AmaWorksheets\Entity
 * @ORM\Entity
 * @ORM\Table(name="worksheet_questions")
 */
class Question
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
     * @ORM\ManyToOne(targetEntity="AmaWorksheets\Entity\Worksheet", inversedBy="questions")
     * @ORM\JoinColumn(name="worksheet_id", referencedColumnName="id", nullable=false)
     */
    protected $worksheet;

    /**
     * @var string
     * @ORM\Column(name="question", type="text", nullable=false)
     */
    protected $question;

    /**
     * @var integer
     * @ORM\Column(name="points", type="integer", length=11)
     */
    protected $points;

    /**
     * @var integer
     * @ORM\Column(name="question_order", type="integer", options={"default" = 0})
     */
    protected $order = 0;

    /**
     * @var integer
     * @ORM\Column(name="answer_type", type="smallint", options={"default" = 1})
     */
    protected $answerType = 1;

    /**
     * @var datetime
     * @ORM\Column(name="date_added", type="datetime")
     */
    protected $dateAdded;

    /**
     * @var array $answers
     * @ORM\OneToMany(targetEntity="AmaWorksheets\Entity\Answer", mappedBy="question", cascade={"persist","remove"}, orphanRemoval=true)
     */
    protected $answers;

    /**
     * @var array $images
     * @ORM\OneToMany(targetEntity="AmaWorksheets\Entity\QuestionImage", mappedBy="question", cascade={"persist","remove"}, orphanRemoval=true)
     */
    protected $images;


    public function __construct()
    {
        $this->dateAdded = new \DateTime();
        $this->answers = new ArrayCollection();
        $this->images = new ArrayCollection();
    }

    /**
     * @param \AmaWorksheets\Entity\datetime $dateAdded
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;
    }

    /**
     * @return \AmaWorksheets\Entity\datetime
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
     * @param string $worksheet
     */
    public function setWorksheet($worksheet)
    {
        $this->worksheet = $worksheet;
    }

    /**
     * @return string
     */
    public function getWorksheet()
    {
        return $this->worksheet;
    }

    /**
     * @param int $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param $answer
     */
    public  function addAnswer($answer)
    {
        $answer->setQuestion($this);
        if(!$this->answers->contains($answer)) {
            $this->answers->add($answer);
        }
    }

    /**
     * @return array|ArrayCollection
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * @param $image
     */
    public function addImage($image)
    {
        $image->setQuestion($this);
        if(!$this->images->contains($image)) {
            $this->images->add($image);
        }
    }

    /**
     * @return array|ArrayCollection
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @param int $answerType
     */
    public function setAnswerType($answerType)
    {
        $this->answerType = $answerType;
    }

    /**
     * @return int
     */
    public function getAnswerType()
    {
        return $this->answerType;
    }


}