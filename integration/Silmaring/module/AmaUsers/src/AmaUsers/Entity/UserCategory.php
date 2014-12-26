<?php

namespace AmaUsers\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class UserCategory
 * @package Amausers\Entity
 * @ORM\Entity(repositoryClass="AmaUsers\Entity\Repository\UserRepository")
 * @ORM\Table(name="user_categories")
 */
class UserCategory
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
     * @ORM\ManyToOne(targetEntity="AmaUsers\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="AmaCategories\Entity\Category", inversedBy="userCategories")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=false)
     */
    protected $category;

    /**
     * @var datetime
     * @ORM\Column(name="date_modified", type="datetime")
     */
    protected $dateModified;

    public function __construct()
    {
        $this->dateModified = new \DateTime();
    }
    /**
     * @param string $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
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

    /**
     * @param mixed $dateModified
     */
    public function setDateModified($dateModified)
    {
        $this->dateModified = $dateModified;
    }

    /**
     * @return mixed
     */
    public function getDateModified()
    {
        return $this->dateModified;
    }



}