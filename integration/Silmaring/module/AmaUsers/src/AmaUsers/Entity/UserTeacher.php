<?php

namespace AmaUsers\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class UserSchool
 * @package Amausers\Entity
 * @ORM\Entity
 * @ORM\Table(name="user_teachers")
 */
class UserTeacher
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
     * @ORM\ManyToOne(targetEntity="AmaUsers\Entity\User", inversedBy="teachers")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="AmaUsers\Entity\Teacher", inversedBy="teacherUsers")
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

}