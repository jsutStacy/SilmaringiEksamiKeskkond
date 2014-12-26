<?php
namespace AmaUsers\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * An example of how to implement a role aware user entity.
 *
 * @ORM\Entity(repositoryClass="AmaUsers\Entity\Repository\UserRepository")
 * @ORM\Table(name="user_alerts")
 *
 */
class Alert
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
     * @ORM\ManyToOne(targetEntity="AmaUsers\Entity\User", inversedBy="alerts")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @var string
     * @ORM\Column(name="message", type="text")
     */
    protected $message;

    /**
     * @var int
     * @ORM\Column(name="status", type="smallint", length=1, options={"default" = 0})
     */
    protected $status = 0;

    /**
     * @var string
     * @ORM\Column(name="date_added", type="datetime")
     */
    protected $dateAdded;


    /**
     * Initialies the roles variable.
     */
    public function __construct()
    {
        $this->dateAdded = new \DateTime();
    }

    /**
     * @param string $dateAdded
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;
    }

    /**
     * @return string
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
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
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param int $status
     */
    public function setStatus($status = 0)
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
