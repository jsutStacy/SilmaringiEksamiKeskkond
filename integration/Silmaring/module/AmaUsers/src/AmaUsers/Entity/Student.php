<?php
namespace AmaUsers\Entity;

use Application\Service\EncryptDecryptService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * An example of how to implement a role aware user entity.
 *
 * @ORM\Entity(repositoryClass="AmaUsers\Entity\Repository\UserRepository")
 * @ORM\Table(name="students")
 *
 */
class Student
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
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     */
    protected $firstName;

    /**
     * @var string
     * @ORM\Column(name="lastname", type="string", length=255, nullable=true)
     */
    protected $lastname;

    /**
     * @var int
     * @ORM\Column(name="status", type="smallint", length=1)
     */
    protected $status = 1;

    /**
     * @var string
     * @ORM\Column(name="personal_code", type="text", nullable=true)
     */
    protected $personalCode;

    /**
     * @var string
     * @ORM\Column(name="personal_code_hash", type="string", length=255, nullable=true)
     */
    protected $personalCodeHash;

    /**
     * @var string
     * @ORM\Column(name="date_added", type="datetime")
     */
    protected $dateAdded;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="AmaSchools\Entity\School", inversedBy="students")
     * @ORM\JoinColumn(name="school_id", referencedColumnName="id")
     */
    protected $school;

    /**
     * @var array $classes
     * @ORM\OneToMany(targetEntity="AmaUsers\Entity\StudentClass", mappedBy="student", cascade={"remove"}, orphanRemoval=true)
     */
    protected $classes;

    /**
     * @var array $studentUsers
     * @ORM\OneToMany(targetEntity="AmaUsers\Entity\UserStudent", mappedBy="student", cascade={"remove"}, orphanRemoval=true)
     */
    protected $studentUsers;

    /**
     * Initialies the roles variable.
     */
    public function __construct()
    {
        $this->dateAdded = new \DateTime();
        $this->studentUsers = new ArrayCollection();
        $this->classes = new ArrayCollection();
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
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
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
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param string $personalCode
     */
    public function setPersonalCode($personalCode)
    {
        $this->setPersonalCodeHash($personalCode);
        if ( $personalCode ) {
            $encrypt = new EncryptDecryptService();
            $this->personalCode =  $encrypt->encrypt($personalCode);
        }
        else {
            $this->personalCode = '';
        }
    }

    /**
     * @param $personalCode
     */
    public function setPersonalCodeHash($personalCode)
    {
        if ( $personalCode ) {
            $encrypt = new EncryptDecryptService();
            $this->personalCodeHash = $encrypt->hashIt($personalCode);
        }
        else {
            $this->personalCodeHash = '';
        }
    }

    /**
     * @return string
     */
    public function getPersonalCodeHash()
    {
        return $this->personalCodeHash;
    }

    /**
     * @return string
     */
    public function getPersonalCode()
    {
        $encrypt = new EncryptDecryptService();
        return $encrypt->decrypt($this->personalCode);
    }

    /**
     * @param int $status
     */
    public function setStatus($status = 1)
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

    public function setSchool($school)
    {
        $this->school = $school;
    }

    public function getSchool()
    {
        return $this->school;
    }

    /**
     * @param \AmaUsers\Entity\StudentClass $class
     */
    public function removeClass(StudentClass $class)
    {
        if ( $this->classes->contains($class) ) {
            $this->classes->removeElement($class);
        }
    }

    public function getClasses()
    {
        return $this->classes;
    }

    public function getStudentUsers()
    {
        return $this->studentUsers;
    }

    public function getFirstStudentUser()
    {
        return $this->studentUsers[0];
    }
}
