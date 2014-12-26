<?php

namespace AmaWorksheets\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class UserWorksheetAnswer
 * @package AmaWorksheets\Entity
 * @ORM\Entity
 * @ORM\Table(name="worksheet_user_results")
 */
class UserWorksheetResult
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
     * @ORM\ManyToOne(targetEntity="AmaWorksheets\Entity\Worksheet")
     * @ORM\JoinColumn(name="worksheet_id", referencedColumnName="id", nullable=false)
     */
    protected $worksheet;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="AmaMaterials\Entity\LessonPlanFile")
     * @ORM\JoinColumn(name="lesson_plan_id", referencedColumnName="id", nullable=false)
     */
    protected $lessonPlan;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="AmaMaterials\Entity\FileClass", inversedBy="worksheetsResults")
     * @ORM\JoinColumn(name="file_class_id", referencedColumnName="id", nullable=false)
     */
    protected $fileClass;

    /**
     * @ORM\ManyToOne(targetEntity="AmaUsers\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     **/
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="AmaUsers\Entity\User")
     * @ORM\JoinColumn(name="sender_id", referencedColumnName="id", nullable=false)
     **/
    protected $sender;

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
     * @param string $fileClass
     */
    public function setFileClass($fileClass)
    {
        $this->fileClass = $fileClass;
    }

    /**
     * @return string
     */
    public function getFileClass()
    {
        return $this->fileClass;
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
     * @param string $lessonPlan
     */
    public function setLessonPlan($lessonPlan)
    {
        $this->lessonPlan = $lessonPlan;
    }

    /**
     * @return string
     */
    public function getLessonPlan()
    {
        return $this->lessonPlan;
    }

    /**
     * @param string $worksheet
     */
    public function setWorksheet($worksheet)
    {
        $this->worksheet = $worksheet;
    }

    /**
     * @return string
     */
    public function getWorksheet()
    {
        return $this->worksheet;
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

    /**
     * @param mixed $sender
     */
    public function setSender($sender)
    {
        $this->sender = $sender;
    }

    /**
     * @return mixed
     */
    public function getSender()
    {
        return $this->sender;
    }



}