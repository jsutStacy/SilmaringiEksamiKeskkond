<?php

namespace AmaSchools\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class SchoolTeacher
 * @package AmaSchools\Entity
 * @ORM\Table(name="school_teachers")
 */
class SchoolTeacher
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
     * @ORM\ManyToOne(targetEntity="AmaUsers\Entity\Teacher", inversedBy="schools")
     * @ORM\JoinColumn(name="teacher_id", referencedColumnName="id")
     */
    protected $teacher;

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
     * @param string $teacher
     */
    public function setTeacher($teacher)
    {
        $this->teacher = $teacher;
    }

    /**
     * @return string
     */
    public function getTeacher()
    {
        return $this->teacher;
    }

}