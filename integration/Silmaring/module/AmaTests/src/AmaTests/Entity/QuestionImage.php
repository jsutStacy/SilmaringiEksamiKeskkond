<?php

namespace AmaTests\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class QuestionImage
 * @package AmaTests\Entity
 * @ORM\Entity
 * @ORM\Table(name="test_question_images")
 */
class QuestionImage
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
     * @ORM\ManyToOne(targetEntity="AmaTests\Entity\Question", inversedBy="images")
     * @ORM\JoinColumn(name="question_id", referencedColumnName="id", nullable=false)
     */
    protected $question;

    /**
     * @var string
     * @ORM\Column(name="filename", type="string", length=255, nullable=false)
     */
    protected $filename;

    /**
     * @var integer
     * @ORM\Column(name="question_image_order", type="integer", options={"default" = 0})
     */
    protected $order = 0;

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
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
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


}