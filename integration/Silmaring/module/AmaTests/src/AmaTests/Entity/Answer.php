<?php

namespace AmaTests\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Answer
 * @package AmaTests\Entity
 * @ORM\Entity
 * @ORM\Table(name="test_answers")
 */
class Answer
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
     * @ORM\ManyToOne(targetEntity="AmaTests\Entity\Question", inversedBy="answers")
     * @ORM\JoinColumn(name="question_id", referencedColumnName="id", nullable=false)
     */
    protected $question;

    /**
     * @var integer
     * @ORM\Column(name="answer_order", type="integer", options={"default" = 0})
     */
    protected $order = 0;

    /**
     * @var string
     * @ORM\Column(name="option_one", type="string", length=255, nullable=true)
     */
    protected $option;

    /**
     * @var string
     * @ORM\Column(name="option_two", type="string", length=255, nullable=true)
     */
    protected $optionTwo;

    /**
     * @var int
     * @ORM\Column(name="is_right", type="smallint", length=1, options={"default" = 0})
     */
    protected $isRight = 0;

    /**
     * @var int
     * @ORM\Column(name="must_contain_words", type="smallint", length=1, options={"default" = 0})
     */
    protected $mustContainWords = 0;

    /**
     * @var string
     * @ORM\Column(name="words", type="text", nullable=true)
     */
    protected $words;

    /**
     * @var datetime
     * @ORM\Column(name="date_added", type="datetime")
     */
    protected $dateAdded;

    /**
     * @var datetime
     * @ORM\Column(name="date_modified", type="datetime")
     */
    protected $dateModified;


    public function __construct()
    {
        $this->dateAdded = new \DateTime();
        $this->dateModified = new \DateTime();
    }

    /**
     * @param datetime $dateAdded
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;
    }

    /**
     * @return datetime
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
     * @param int $mustContainWords
     */
    public function setMustContainWords($mustContainWords)
    {
        $this->mustContainWords = $mustContainWords;
    }

    /**
     * @return int
     */
    public function getMustContainWords()
    {
        return $this->mustContainWords;
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
     * @param string $option
     */
    public function setOption($option)
    {
        $this->option = $option;
    }

    /**
     * @return string
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * @param string $optionTwo
     */
    public function setOptionTwo($optionTwo)
    {
        $this->optionTwo = $optionTwo;
    }

    /**
     * @return string
     */
    public function getOptionTwo()
    {
        return $this->optionTwo;
    }

    /**
     * @param string $words
     */
    public function setWords($words)
    {
        $this->words = $words;
    }

    /**
     * @return string
     */
    public function getWords()
    {
        return $this->words;
    }

    /**
     * @param \DateTime $dateModified
     */
    public function setDateModified($dateModified)
    {
        $this->dateModified = $dateModified;
    }

    /**
     * @return \DateTime
     */
    public function getDateModified()
    {
        return $this->dateModified;
    }



}