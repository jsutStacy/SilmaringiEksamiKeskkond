<?php

namespace AmaCategories\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Category
 * @package Amausers\Entity
 * @ORM\Entity(repositoryClass="AmaCategories\Entity\Repository\CategoryRepository")
 * @ORM\Table(name="categories")
 */
class Category
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
     * @ORM\Column(name="category_name", type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="Category", mappedBy="parent", cascade={"remove","persist"}, orphanRemoval=true)
     **/
    protected $children;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     **/
    protected $parent;

    /**
     * @var integer
     * @ORM\Column(name="cat_order", type="integer", options={"default" = 0})
     */
    protected $order = 0;

    /**
     * @var integer
     * @ORM\Column(name="cat_depth", type="integer", options={"default" = 0})
     */
    protected $depth = 0;

    /**
     * @var integer
     * @ORM\Column(name="cat_left", type="integer", options={"default" = 0})
     */
    protected $left = 0;

    /**
     * @var integer
     * @ORM\Column(name="cat_right", type="integer", options={"default" = 0})
     */
    protected $right = 0;

    /**
     * @var integer
     * @ORM\Column(name="status", type="smallint", length=1, options={"default" = 1})
     */
    protected $status = 1;

    /**
     * @var string
     * @ORM\Column(name="category_lang", type="string", length=10, nullable=true)
     */
    protected $language;

    /**
     * @var datetime
     * @ORM\Column(name="date_added", type="datetime")
     */
    protected $dateAdded;

    /**
     * @var array $files
     * @ORM\OneToMany(targetEntity="AmaMaterials\Entity\File", mappedBy="category", cascade={"remove", "persist"}, orphanRemoval=true)
     */
    protected $files;

    /**
     * @var array $tests
     * @ORM\OneToMany(targetEntity="AmaTests\Entity\Test", mappedBy="category", cascade={"remove"}, orphanRemoval=true)
     */
    protected $tests;

    /**
     * @var array $worksheets
     * @ORM\OneToMany(targetEntity="AmaWorksheets\Entity\Worksheet", mappedBy="category", cascade={"remove"}, orphanRemoval=true)
     */
    protected $worksheets;

    /**
     * @var array $lessonPlanFiles
     * @ORM\OneToMany(targetEntity="AmaMaterials\Entity\LessonPlanFile", mappedBy="category", cascade={"remove"}, orphanRemoval=true)
     */
    protected $lessonPlanFiles;

    /**
     * @var array $userCategories
     * @ORM\OneToMany(targetEntity="AmaUsers\Entity\UserCategory", mappedBy="category", cascade={"remove"}, orphanRemoval=true)
     */
    protected $userCategories;


    public function __construct()
    {
        $this->dateAdded = new \DateTime();
        $this->children = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->tests = new ArrayCollection();
        $this->worksheets = new ArrayCollection();
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
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param int $order
     */
    public function setOrder($order = 0)
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


    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    /**
     * @param int $status
     */
    public function setStatus($status = 1)
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setLanguage($language)
    {
        $this->language = $language;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function setParent($parent = null)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param int $depth
     */
    public function setDepth($depth)
    {
        $this->depth = $depth;
    }

    /**
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * @param array $files
     */
    public function setFiles($files)
    {
        $this->files = $files;
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param array $tests
     */
    public function setTests($tests)
    {
        $this->tests = $tests;
    }

    /**
     * @return array
     */
    public function getTests()
    {
        return $this->tests;
    }

    /**
     * @param array $worksheets
     */
    public function setWorksheets($worksheets)
    {
        $this->worksheets = $worksheets;
    }

    /**
     * @return array
     */
    public function getWorksheets()
    {
        return $this->worksheets;
    }

    /**
     * @param int $left
     */
    public function setLeft($left)
    {
        $this->left = $left;
    }

    /**
     * @return int
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * @param array $lessonPlanFiles
     */
    public function setLessonPlanFiles($lessonPlanFiles)
    {
        $this->lessonPlanFiles = $lessonPlanFiles;
    }

    /**
     * @return array
     */
    public function getLessonPlanFiles()
    {
        return $this->lessonPlanFiles;
    }

    /**
     * @param int $right
     */
    public function setRight($right)
    {
        $this->right = $right;
    }

    /**
     * @return int
     */
    public function getRight()
    {
        return $this->right;
    }

    /**
     * @param array $userCategories
     */
    public function setUserCategories($userCategories)
    {
        $this->userCategories = $userCategories;
    }

    /**
     * @return array
     */
    public function getUserCategories()
    {
        return $this->userCategories;
    }



}