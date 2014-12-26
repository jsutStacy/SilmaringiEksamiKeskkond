<?php
namespace AmaSchools\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity(repositoryClass="AmaSchools\Entity\Repository\SchoolRepository")
 * @ORM\Table(name="schools")
 *
 */
class School
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
     * @ORM\Column(name="short_name", type="string", length=255)
     */
    protected $shortName;

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
     * @var array $users
     * @ORM\OneToMany(targetEntity="AmaUsers\Entity\UserSchool", mappedBy="school", cascade={"remove"}, orphanRemoval=true)
     */
    protected $users;

    /**
     * @var array $classes
     * @ORM\OneToMany(targetEntity="AmaSchools\Entity\SchoolClass", mappedBy="school", cascade={"remove"}, orphanRemoval=true)
     */
    protected $classes;

    /**
     * @var array $teachers
     * @ORM\OneToMany(targetEntity="AmaUsers\Entity\Teacher", mappedBy="school", cascade={"remove"}, orphanRemoval=true)
     */
    protected $teachers;

    /**
     * @var array $students
     * @ORM\OneToMany(targetEntity="AmaUsers\Entity\Student", mappedBy="school", cascade={"remove"}, orphanRemoval=true)
     */
    protected $students;


    public function __construct()
    {
        $this->date_added = new \DateTime();
        $this->users  = new ArrayCollection();
        $this->classes = new ArrayCollection();
        $this->teachers = new ArrayCollection();
        $this->students = new ArrayCollection();
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

    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param SchoolClass $class
     */
    public function addClass(SchoolClass $class)
    {
        if ( !$this->classes->contains($class) ) {
            $this->classes[] = $class;
        }
    }

    /**
     * @param SchoolClass $class
     */
    public function removeClass(SchoolClass $class)
    {
        if ( $this->classes->contains($class) ) {
             $this->classes->removeElement($class);
        }
    }

    /**
     * Remove all classes from school
     */
    public function removeAllClasses()
    {
        foreach($this->classes as $class) {
            $this->classes->removeElement($class);
        }
    }

    public function getClasses()
    {
        return $this->classes;
    }

    public function getTeachers()
    {
        return $this->teachers;
    }

    public function getStudents()
    {
        return $this->students;
    }

    /**
     * @param string $shortName
     */
    public function setShortName($shortName)
    {
        $this->shortName = $shortName;
    }

    /**
     * @return string
     */
    public function getShortName()
    {
        return $this->shortName;
    }




}