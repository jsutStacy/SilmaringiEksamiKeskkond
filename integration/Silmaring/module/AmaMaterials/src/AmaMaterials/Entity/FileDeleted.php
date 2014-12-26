<?php

namespace AmaMaterials\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class FileDeleted
 * @package AmaSchools\Entity
 * @ORM\Entity
 * @ORM\Table(name="file_deleted")
 */
class FileDeleted
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
     * @ORM\ManyToOne(targetEntity="AmaMaterials\Entity\File", inversedBy="deletedFiles")
     * @ORM\JoinColumn(name="file_id", referencedColumnName="id", nullable=false)
     */
    protected $file;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="AmaUsers\Entity\User")
     * @ORM\JoinColumn(name="deleter_id", referencedColumnName="id", nullable=false)
     */
    protected $deleter;

    /**
     * @var datetime
     * @ORM\Column(name="date_deleted", type="datetime")
     */
    protected $dateDeleted;


    public function __construct()
    {
        $this->dateDeleted = new \DateTime();
    }

    /**
     * @param \AmaMaterials\Entity\datetime $dateDeleted
     */
    public function setDateDeleted($dateDeleted)
    {
        $this->dateDeleted = $dateDeleted;
    }

    /**
     * @return \AmaMaterials\Entity\datetime
     */
    public function getDateDeleted()
    {
        return $this->dateDeleted;
    }

    /**
     * @param string $deleter
     */
    public function setDeleter($deleter)
    {
        $this->deleter = $deleter;
    }

    /**
     * @return string
     */
    public function getDeleter()
    {
        return $this->deleter;
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




}