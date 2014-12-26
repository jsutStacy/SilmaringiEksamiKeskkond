<?php

namespace AmaUsers\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class UserSchool
 * @package Amausers\Entity
 * @ORM\Entity
 * @ORM\Table(name="user_schools")
 */
class UserSchool
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
     * @ORM\ManyToOne(targetEntity="AmaUsers\Entity\User", inversedBy="schools")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="AmaSchools\Entity\School", inversedBy="users")
     * @ORM\JoinColumn(name="school_id", referencedColumnName="id")
     */
    protected $school;

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