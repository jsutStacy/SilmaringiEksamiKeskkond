<?php

namespace AmaSchools\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class SchoolClass
 * @package AmaSchools\Entity
 * @ORM\Entity
 * @ORM\Table(name="school_classes")
 */
class SchoolClass
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
     * @ORM\ManyToOne(targetEntity="AmaSchools\Entity\School", inversedBy="classes")
     * @ORM\JoinColumn(name="school_id", referencedColumnName="id")
     */
    protected $school;

    /**
     * @var string
     * @ORM\Column(name="class_name", type="string", length=255)
     */
    protected $className;

    /**
     * @var array $teachersInClass
     * @ORM\OneToMany(targetEntity="AmaUsers\Entity\TeacherClass", mappedBy="class", cascade={"remove"}, orphanRemoval=true)
     */
    protected $teachersInClass;

    /**
     * @var array $studentsInClass
     * @ORM\OneToMany(targetEntity="AmaUsers\Entity\StudentClass", mappedBy="class", cascade={"remove"}, orphanRemoval=true)
     */
    protected $studentsInClass;

    /**
     * @var array $files
     * @ORM\OneToMany(targetEntity="AmaMaterials\Entity\FileClass", mappedBy="class", cascade={"remove"}, orphanRemoval=true)
     */
    protected $files;

    public function __construct()
    {
        $this->teachersInClass = new ArrayCollection();
        $this->studentsInClass = new ArrayCollection();
        $this->files = new ArrayCollection();
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
     * @param string $school
     */
    public function setSchool($school)
    {
        $this->school = $school;
    }

    /**
     * @return string
     */
    public function getSchool()
    {
        return $this->school;
    }

    /**
     * @param string $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    public function getTeachersInClass()
    {
        return $this->teachersInClass;
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function getStudentsInClass()
    {
        return $this->studentsInClass;
    }
}