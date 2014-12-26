<?php

namespace AmaUsers\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Http\PhpEnvironment\RemoteAddress;

/**
 * Class UserLogin
 * @package Amausers\Entity
 * @ORM\Entity
 * @ORM\Table(name="user_logins")
 */
class UserLogin
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
     * @ORM\ManyToOne(targetEntity="AmaUsers\Entity\User", inversedBy="userLogins")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @var string
     * @ORM\Column(name="date_added", type="datetime")
     */
    protected $date_added;

    /**
     * @var string
     * @ORM\Column(name="date_ended", type="datetime", nullable=true)
     */
    protected $date_ended;

    /**
     * @var text
     * @ORM\Column(name="ip", type="text", nullable=true)
     */
    protected $ip;


    public function __construct()
    {
        $this->date_added = new \DateTime();
        $this->setIp();
    }

    /**
     * @param string $date_added
     */
    public function setDateAdded($date_added)
    {
        $this->date_added = $date_added;
    }

    /**
     * @return string
     */
    public function getDateAdded()
    {
        return $this->date_added;
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
     * Set ip
     */
    public function setIp()
    {
        $remote = new RemoteAddress;
        $this->ip = $remote->getIpAddress();
    }

    /**
     * @return \AmaUsers\Entity\text
     */
    public function getIp()
    {
        return $this->ip;
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
     * @param string $date_ended
     */
    public function setDateEnded($date_ended)
    {
        $this->date_ended = $date_ended;
    }

    /**
     * @return string
     */
    public function getDateEnded()
    {
        return $this->date_ended;
    }





}