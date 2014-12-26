<?php

namespace AmaMaterials\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class LessonPlanFile
 * @package AmaSchools\Entity
 * @ORM\Entity(repositoryClass="AmaMaterials\Entity\Repository\LessonPlanRepository")
 * @ORM\Table(name="lesson_plan_files")
 */
class LessonPlanFile
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
     * @ORM\ManyToOne(targetEntity="AmaMaterials\Entity\File", inversedBy="lessonPlanFiles")
     * @ORM\JoinColumn(name="file_id", referencedColumnName="id", nullable=false)
     */
    protected $file;

    /**
     * @var string
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    protected $comment;

    /**
     * @var string
     * @ORM\Column(name="type", type="string", length=255)
     */
    protected $type = 'image';

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="AmaUsers\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="AmaCategories\Entity\Category", inversedBy="lessongPlanFiles")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=false)
     */
    protected $category;

    /**
     * @var integer
     * @ORM\Column(name="priority", type="integer")
     */
    protected $priority= 0;

    /**
     * @var datetime
     * @ORM\Column(name="date_added", type="datetime")
     */
    protected $dateAdded;


    /**
     * @var array $views
     * @ORM\OneToMany(targetEntity="AmaMaterials\Entity\FileView", mappedBy="file", cascade={"remove"}, orphanRemoval=true)
     */
    protected $views;

    /**
     * @var array $fileClasses
     * @ORM\OneToMany(targetEntity="AmaMaterials\Entity\FileClass", mappedBy="file", cascade={"remove"}, orphanRemoval=true)
     */
    protected $fileClasses;

    /**
     * @var array $userTestAnsweredQuestions
     * @ORM\OneToMany(targetEntity="AmaTests\Entity\UserTestAnsweredQuestion", mappedBy="lessonPlan", cascade={"remove"}, orphanRemoval=true)
     */
    protected $userTestAnsweredQuestions;

    /**
     * @var array $userWorksheetAnsweredQuestions
     * @ORM\OneToMany(targetEntity="AmaWorksheets\Entity\UserWorksheetAnsweredQuestion", mappedBy="lessonPlan", cascade={"remove"}, orphanRemoval=true)
     */
    protected $userWorksheetAnsweredQuestions;


    public function __construct()
    {
        $this->dateAdded = new \DateTime();
        $this->fileClasses = new ArrayCollection();
        $this->views = new ArrayCollection();
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
     * @param string $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
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
     * @param string $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param int $priority
     */
    public function setPriority($priority = 0)
    {
        $this->priority = $priority;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
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
     * @param string $type
     */
    public function setType($type = 'image')
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    public function getViews()
    {
        return $this->viewes;
    }

}