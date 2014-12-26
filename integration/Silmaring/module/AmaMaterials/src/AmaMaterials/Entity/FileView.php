<?php

namespace AmaMaterials\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class FileView
 * @package AmaSchools\Entity
 * @ORM\Entity
 * @ORM\Table(name="file_views")
 */
class FileView
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
     * @ORM\ManyToOne(targetEntity="AmaMaterials\Entity\LessonPlanFile", inversedBy="views")
     * @ORM\JoinColumn(name="file_id", referencedColumnName="id", nullable=false)
     */
    protected $file;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="AmaMaterials\Entity\FileClass")
     * @ORM\JoinColumn(name="file_class_id", referencedColumnName="id", nullable=false)
     */
    protected $fileClass;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="AmaUsers\Entity\User", inversedBy="viewdFiles")
     * @ORM\JoinColumn(name="viewer_id", referencedColumnName="id", nullable=false)
     */
    protected $viewer;

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
     * @param string $viewer
     */
    public function setViewer($viewer)
    {
        $this->viewer = $viewer;
    }

    /**
     * @return string
     */
    public function getViewer()
    {
        return $this->viewer;
    }

    /**
     * @param string $fileClass
     */
    public function setFileClass($fileClass)
    {
        $this->fileClass = $fileClass;
    }

    public function getFileClass()
    {
        return $this->fileClass;
    }
}