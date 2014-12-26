<?php
namespace AmaTests\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AmaTests\Entity\Repository\TestRepository")
 * @ORM\Table(name="tests")
 */
class Test
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    protected $comment;

    /**
     * @var string
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @var int
     * @ORM\Column(name="status", type="smallint", length=1, options={"default" = 1})
     */
    protected $status = 1;

    /**
     * @var datetime
     * @ORM\Column(name="date_added", type="datetime")
     */
    protected $date_added;

    /**
     * @ORM\ManyToOne(targetEntity="AmaUsers\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     **/
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="AmaCategories\Entity\Category", inversedBy="tests")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     **/
    protected $category;

    /**
     * @ORM\OneToOne(targetEntity="AmaMaterials\Entity\File", mappedBy="test")
     */
    protected $file;

    /**
     * @var array $questions
     * @ORM\OrderBy({"order" = "ASC"})
     * @ORM\OneToMany(targetEntity="AmaTests\Entity\Question", mappedBy="test", cascade={"persist","remove"}, orphanRemoval=true)
     */
    protected $questions;

    public function __construct()
    {
        $this->date_added = new \DateTime();
        $this->questions = new ArrayCollection();
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
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
     * @param \AmaTests\Entity\datetime $date_added
     */
    public function setDateAdded($date_added)
    {
        $this->date_added = $date_added;
    }

    /**
     * @return \AmaTests\Entity\datetime
     */
    public function getDateAdded()
    {
        return $this->date_added;
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
     * @param int $status
     */
    public function setStatus($status)
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
     * @param mixed $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param $question
     */
    public function addQuestion($question)
    {
        $question->setTest($this);
        if(!$this->questions->contains($question)) {
            $this->questions->add($question);
        }
    }

    /**
     * @return array|ArrayCollection
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }


}