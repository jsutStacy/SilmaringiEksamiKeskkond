<?php

namespace AmaUsers\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class TeacherClass
 * @package AmaSchools\Entity
 * @ORM\Entity
 * @ORM\Table(name="student_classes")
 */
class StudentClass
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
     * @ORM\ManyToOne(targetEntity="AmaSchools\Entity\School")
     * @ORM\JoinColumn(name="school_id", referencedColumnName="id")
     */
    protected $school;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="AmaUsers\Entity\Student", inversedBy="classes")
     * @ORM\JoinColumn(name="student_id", referencedColumnName="id")
     */
    protected $student;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="AmaSchools\Entity\SchoolClass", inversedBy="studentsInClass")
     * @ORM\JoinColumn(name="class_id", referencedColumnName="id")
     */
    protected $class;

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
     * @param $student
     */
    public function setStudent($student)
    {
        $this->student = $student;
    }

    /**
     * @return string
     */
    public function getStudent()
    {
        return $this->student;
    }

}