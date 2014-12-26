<?php

namespace AmaWorksheets\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class TempImage
 * @package AmaWorksheets\Entity
 * @ORM\Entity
 * @ORM\Table(name="worksheet_temp_images")
 */
class TempImage
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
     * @ORM\Column(name="filename", type="string", length=255)
     */
    protected $filename;

    /**
     * @var integer
     * @ORM\Column(name="cat_order", type="integer", options={"default" = 0})
     */
    protected $order = 0;

    /**
     * @ORM\ManyToOne(targetEntity="AmaUsers\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     **/
    protected $user;

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
     * @param \AmaWorksheets\Entity\datetime $dateAdded
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;
    }

    /**
     * @return \AmaWorksheets\Entity\datetime
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
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
     * @param int $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }



}