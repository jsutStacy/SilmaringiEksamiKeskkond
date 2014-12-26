<?php
namespace AmaMaterials\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AmaMaterials\Entity\Repository\MaterialRepository")
 * @ORM\Table(name="files")
 */
class File
{

    const TYPE_FILE = 'file';
    const TYPE_PRESENTATION = 'presentation';
    const TYPE_IMAGE = 'image';
    const TYPE_VIDEO = 'video';
    const TYPE_TEST = 'test';
    const TYPE_WORKSHEET = 'worksheet';

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
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @var string
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    protected $comment;

    /**
     * @var string
     * @ORM\Column(name="filename", type="string", length=255, nullable=true)
     */
    protected $filename;

    /**
     * @var string
     * @ORM\Column(name="video", type="string", length=255, nullable=true)
     */
    protected $video;

    /**
     * @var int
     * @ORM\Column(name="status", type="smallint", length=1, options={"default" = 1})
     */
    protected $status = 1;

    /**
     * @var string
     * @ORM\Column(name="type", type="string", length=255)
     */
    protected $type = 'file';

    /**
     * @var datetime
     * @ORM\Column(name="date_added", type="datetime")
     */
    protected $date_added;

    /**
     * @ORM\ManyToOne(targetEntity="AmaUsers\Entity\User", inversedBy="files")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     **/
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="AmaCategories\Entity\Category", inversedBy="files")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     **/
    protected $category;

    /**
     * @ORM\OneToOne(targetEntity="AmaTests\Entity\Test", inversedBy="file")
     * @ORM\JoinColumn(name="test_id", referencedColumnName="id")
     **/
    protected $test;

    /**
     * @ORM\OneToOne(targetEntity="AmaWorksheets\Entity\Worksheet", inversedBy="file")
     * @ORM\JoinColumn(name="worksheet_id", referencedColumnName="id")
     **/
    protected $worksheet;

    /**
     * @var array $deletedFiles
     * @ORM\OneToMany(targetEntity="AmaMaterials\Entity\FileDeleted", mappedBy="file", cascade={"remove"}, orphanRemoval=true)
     */
    protected $deletedFiles;

    /**
     * @var array $lessonPlanFiles
     * @ORM\OneToMany(targetEntity="AmaMaterials\Entity\LessonPlanFile", mappedBy="file", cascade={"remove"}, orphanRemoval=true)
     */
    protected $lessonPlanFiles;

    public function __construct()
    {
        $this->date_added = new \DateTime();
        $this->classesSent = new ArrayCollection();
        $this->deletedFiles = new ArrayCollection();
        $this->lessonPlanFiles = new ArrayCollection();
    }

    /**
     * @param array $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return array
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param \AmaSchools\Entity\datetime $date_added
     */
    public function setDateAdded($date_added)
    {
        $this->date_added = $date_added;
    }

    /**
     * @return \AmaSchools\Entity\datetime
     */
    public function getDateAdded()
    {
        return $this->date_added;
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
     * @param array $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return array
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $type
     */
    public function setType($type = self::TYPE_FILE)
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

    public function getClassesSent()
    {
        return $this->classesSent;
    }

    /**
     * @param string $video
     */
    public function setVideo($video)
    {
        $this->video = $video;
    }

    /**
     * @return string
     */
    public function getVideo()
    {
        return $this->video;
    }

    /**
     * @param mixed $test
     */
    public function setTest($test)
    {
        $this->test = $test;
    }

    /**
     * @return mixed
     */
    public function getTest()
    {
        return $this->test;
    }

    /**
     * @param mixed $worksheet
     */
    public function setWorksheet($worksheet)
    {
        $this->worksheet = $worksheet;
    }

    /**
     * @return mixed
     */
    public function getWorksheet()
    {
        return $this->worksheet;
    }

    /**
     * @param array $deletedFiles
     */
    public function setDeletedFiles($deletedFiles)
    {
        $this->deletedFiles = $deletedFiles;
    }

    /**
     * @return array
     */
    public function getDeletedFiles()
    {
        return $this->deletedFiles;
    }



}