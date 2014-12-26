<?php

namespace AmaMaterials\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class FileClass
 * @package AmaSchools\Entity
 * @ORM\Entity
 * @ORM\Table(name="file_classes")
 */
class FileClass
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
     * @ORM\ManyToOne(targetEntity="AmaMaterials\Entity\LessonPlanFile", inversedBy="fileClasses")
     * @ORM\JoinColumn(name="file_id", referencedColumnName="id", nullable=false)
     */
    protected $file;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="AmaSchools\Entity\SchoolClass", inversedBy="files")
     * @ORM\JoinColumn(name="class_id", referencedColumnName="id", nullable=false)
     */
    protected $class;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="AmaUsers\Entity\User", inversedBy="sentFiles")
     * @ORM\JoinColumn(name="sender_id", referencedColumnName="id", nullable=false)
     */
    protected $sender;

    /**
     * @var datetime
     * @ORM\Column(name="date_added", type="datetime")
     */
    protected $dateAdded;

    /**
     * @var array $worksheetUserAnsweredQuestions
     * @ORM\OneToMany(targetEntity="AmaWorksheets\Entity\UserWorksheetAnsweredQuestion", mappedBy="fileClass", cascade={"remove"}, orphanRemoval=true)
     */
    protected $worksheetUserAnsweredQuestions;

    /**
     * @var array $testUserAnsweredQuestions
     * @ORM\OneToMany(targetEntity="AmaTests\Entity\UserTestAnsweredQuestion", mappedBy="fileClass", cascade={"remove"}, orphanRemoval=true)
     */
    protected $testUserAnsweredQuestions;

    /**
     * @var array $testsResults
     * @ORM\OneToMany(targetEntity="AmaTests\Entity\UserTestResult", mappedBy="fileClass", cascade={"remove"}, orphanRemoval=true)
     */
    protected $testsResults;

    /**
     * @var array $worksheetsResults
     * @ORM\OneToMany(targetEntity="AmaWorksheets\Entity\UserWorksheetResult", mappedBy="fileClass", cascade={"remove"}, orphanRemoval=true)
     */
    protected $worksheetsResults;

    /**
     * @var array $userTestAnswers
     * @ORM\OneToMany(targetEntity="AmaTests\Entity\UserTestAnswer", mappedBy="fileClass", cascade={"remove"}, orphanRemoval=true)
     */
    protected $userTestAnswers;

    /**
     * @var array $userWorksheetAnswers
     * @ORM\OneToMany(targetEntity="AmaWorksheets\Entity\UserWorksheetAnswer", mappedBy="fileClass", cascade={"remove"}, orphanRemoval=true)
     */
    protected $userWorksheetAnswers;


    public function __construct()
    {
        $this->dateAdded = new \DateTime();
        $this->worksheetUserAnsweredQuestions = new ArrayCollection();
        $this->worksheetsResults = new ArrayCollection();
    }

    /**
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param \AmaMaterials\Entity\datetime $dateAdded
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;
    }

    /**
     * @return \AmaMaterials\Entity\datetime
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
     * @param string $sender
     */
    public function setSender($sender)
    {
        $this->sender = $sender;
    }

    /**
     * @return string
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param string $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param array $worksheetUserAnsweredQuestions
     */
    public function setWorksheetUserAnsweredQuestions($worksheetUserAnsweredQuestions)
    {
        $this->worksheetUserAnsweredQuestions = $worksheetUserAnsweredQuestions;
    }

    /**
     * @return array
     */
    public function getWorksheetUserAnsweredQuestions()
    {
        return $this->worksheetUserAnsweredQuestions;
    }
}