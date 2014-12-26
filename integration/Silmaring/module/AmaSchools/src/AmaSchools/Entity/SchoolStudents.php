<?php

namespace AmaSchools\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class SchoolStudent
 * @package AmaSchools\Entity
 * @ORM\Table(name="school_students")
 */
class SchoolStudent
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
     * @ORM\ManyToOne(targetEntity="AmaUsers\Entity\Student", inversedBy="schools")
     * @ORM\JoinColumn(name="student_id", referencedColumnName="id")
     */
    protected $student;

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
     * @param string $student
     */
    public function setStudent($student)
    {
        $this->$tudent = $student;
    }

    /**
     * @return string
     */
    public function getStudent()
    {
        return $this->student;
    }

}