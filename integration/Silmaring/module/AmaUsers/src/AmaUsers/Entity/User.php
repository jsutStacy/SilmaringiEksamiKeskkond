<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

namespace AmaUsers\Entity;

use Application\Service\EncryptDecryptService;
use BjyAuthorize\Provider\Role\ProviderInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Zend\Http\PhpEnvironment\RemoteAddress;
use ZfcUser\Entity\UserInterface;

/**
 * An example of how to implement a role aware user entity.
 *
 * @ORM\Entity(repositoryClass="AmaUsers\Entity\Repository\UserRepository")
 * @ORM\Table(name="users")
 *
 */
class User implements UserInterface, ProviderInterface
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
     * @ORM\Column(type="string", length=255, unique=true, nullable=true)
     */
    protected $username;

    /**
     * @var string
     * @ORM\Column(type="string", unique=true,  length=255)
     */
    protected $email;

    /**
     * @var string
     * @ORM\Column(name="displayName", type="string", length=255, nullable=true)
     */
    protected $displayName;

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
     * @var string
     * @ORM\Column(name="password", type="string", length=255)
     */
    protected $password;

    /**
     * @var string
     * @ORM\Column(name="temp_password", type="string", length=255, nullable=true)
     */
    protected $tempPassword;

    /**
     * @var string
     * @ORM\Column(name="dynamic_salt", type="string", length=255)
     */
    protected $dynamicSalt;

    /**
     * @var string
     * @ORM\Column(name="temp_dynamic_salt", type="string", length=255, nullable=true)
     */
    protected $tempDynamicSalt;

    /**
     * @var int
     * @ORM\Column(name="state", type="smallint", length=1)
     */
    protected $state;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\ManyToMany(targetEntity="AmaUsers\Entity\Role")
     * @ORM\JoinTable(name="user_role_linker",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     * )
     */
    protected $roles;


    /**
     * @var string
     * @ORM\Column(name="fb_id", type="string", length=255, nullable=true)
     */
    protected $fbId;

    /**
     * @var string
     * @ORM\Column(name="google_id", type="string", length=255, nullable=true)
     */
    protected $googleId;

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
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    protected $image;

    /**
     * @var string
     * @ORM\Column(name="date_register", type="datetime")
     */
    protected $dateRegister;

    /**
     * @var string
     * @ORM\Column(name="ip", type="string", length=100, nullable=true)
     */
    protected $ip;

    /**
     * @var array $userLogins
     * @ORM\OneToMany(targetEntity="AmaUsers\Entity\UserLogin", mappedBy="user", cascade={"remove"}, orphanRemoval=true)
     */
    protected $userLogins;

    /**
     * @var array $schools
     * @ORM\OneToMany(targetEntity="AmaUsers\Entity\UserSchool", mappedBy="user", cascade={"remove", "persist"}, orphanRemoval=true)
     */
    protected $schools;

    /**
     * @var array $teachers
     * @ORM\OneToMany(targetEntity="AmaUsers\Entity\UserTeacher", mappedBy="user", cascade={"remove", "persist"}, orphanRemoval=true)
     */
    protected $teachers;

    /**
     * @var array $students
     * @ORM\OneToMany(targetEntity="AmaUsers\Entity\UserStudent", mappedBy="user", cascade={"remove", "persist"}, orphanRemoval=true)
     */
    protected $students;

    /**
     * @var array $files
     * @ORM\OneToMany(targetEntity="AmaMaterials\Entity\File", mappedBy="user", cascade={"remove", "persist"}, orphanRemoval=true)
     */
    protected $files;

    /**
     * @var array $sentFiles
     * @ORM\OneToMany(targetEntity="AmaMaterials\Entity\FileClass", mappedBy="sender", cascade={"remove", "persist"}, orphanRemoval=true)
     */
    protected $sentFiles;

    /**
     * @var array $viewdFiles
     * @ORM\OneToMany(targetEntity="AmaMaterials\Entity\FileView", mappedBy="viewer", cascade={"remove", "persist"}, orphanRemoval=true)
     */
    protected $viewdFiles;

    /**
     * @var array $alerts
     * @ORM\OneToMany(targetEntity="AmaUsers\Entity\Alert", mappedBy="user", cascade={"remove", "persist"}, orphanRemoval=true)
     */
    protected $alerts;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="AmaUsers\Entity\UserLogin")
     * @ORM\JoinColumn(name="user_last_login", referencedColumnName="id")
     */
    protected $userLastLogin;


    /**
     * Initialies the roles variable.
     */
    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->dateRegister = new \DateTime();
        $this->userLogins = new ArrayCollection();
        $this->schools = new ArrayCollection();
        $this->teachers = new ArrayCollection();
        $this->students = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->sentFiles = new ArrayCollection();
        $this->viewedFiles = new ArrayCollection();
        $this->alerts = new ArrayCollection();
        $this->setIp();
    }

    /**
     * @param string $dateRegister
     */
    public function setDateRegister($dateRegister)
    {
        $this->dateRegister = $dateRegister;
    }

    /**
     * @return string
     */
    public function getDateRegister()
    {
        return $this->dateRegister;
    }

    /**
     * @param string $dynamicSalt
     */
    public function setDynamicSalt($dynamicSalt)
    {
        $this->dynamicSalt = $dynamicSalt;
    }

    /**
     * @return string
     */
    public function getDynamicSalt()
    {
        return $this->dynamicSalt;
    }

    /**
     * @param string $email
     * @return void|\ZfcUser\Entity\UserInterface
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $fbId
     */
    public function setFbId($fbId)
    {
        $this->fbId = $fbId;
    }

    /**
     * @return string
     */
    public function getFbId()
    {
        return $this->fbId;
    }

    /**
     * @param $firstName
     * @internal param string $firstname
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
     * @return void|\ZfcUser\Entity\UserInterface
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
     * @param string $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    public function setIp()
    {
        $remote = new RemoteAddress;
        $this->ip = $remote->getIpAddress();
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
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
     * @param string $password
     * @return void|\ZfcUser\Entity\UserInterface
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $roles
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param $role
     * @return bool
     */
    public function hasRole($role)
    {
        foreach ( $this->roles as $userRole ) {
            if ($userRole->getRoleId() == $role ) return true;
        }

        return false;
    }

    /**
     * @return role
     */
    public function getSingleRole()
    {
        return $this->roles[0];
    }

    /**
     * @param int $state
     * @return void|\ZfcUser\Entity\UserInterface
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $tempDynamicSalt
     */
    public function setTempDynamicSalt($tempDynamicSalt)
    {
        $this->tempDynamicSalt = $tempDynamicSalt;
    }

    /**
     * @return string
     */
    public function getTempDynamicSalt()
    {
        return $this->tempDynamicSalt;
    }

    /**
     * @param string $tempPassword
     */
    public function setTempPassword($tempPassword)
    {
        $this->tempPassword = $tempPassword;
    }

    /**
     * @return string
     */
    public function getTempPassword()
    {
        return $this->tempPassword;
    }

    /**
     * @param string $username
     * @return void|\ZfcUser\Entity\UserInterface
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $displayName
     * @return void|\ZfcUser\Entity\UserInterface
     */
    public function setDisplayName($displayName)
    {
        $this->displayName =  $displayName;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * Add a role to the user.
     *
     * @param Role $role
     *
     * @return void
     */
    public function addRole($role)
    {
        $this->roles[] = $role;
    }

    /**
     * @param string $googleId
     */
    public function setGoogleId($googleId)
    {
        $this->googleId = $googleId;
    }

    /**
     * @return string
     */
    public function getGoogleId()
    {
        return $this->googleId;
    }

    /**
     * Get userlogins
     * @return array|ArrayCollection
     */
    public function getUserLogins()
    {
        return $this->userLogins;
    }

    public function getSchools()
{
    return $this->schools;
}

    /**
     * @param $school
     */
    public function addSchool($school)
    {
        $this->schools->removeElement($school);
        if (!$this->schools->contains($school)) {
            $this->schools[] = $school;
        }
    }

    public function removeTeacher($teacher)
    {
        if ($this->teachers->contains($teacher)) {
            $this->teachers->removeElement($teacher);
        }
    }

    public function getTeachers()
    {
        return $this->teachers;
    }

    public function removeStudent($student)
    {
        if ($this->students->contains($student)) {
            $this->students->removeElement($student);
        }
    }

    public function getStudents()
    {
        return $this->students;
    }

    public function getSentFiles()
    {
        return $this->sentFiles;
    }

    public function getViewedFiles()
    {
        return $this->viewedFiles;
    }

    public function getAlerts()
    {
        return $this->alerts;
    }

    /**
     * @param array $files
     */
    public function setFiles($files)
    {
        $this->files = $files;
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param mixed $userLastLogin
     */
    public function setUserLastLogin($userLastLogin)
    {
        $this->userLastLogin = $userLastLogin;
    }

    /**
     * @return mixed
     */
    public function getUserLastLogin()
    {
        return $this->userLastLogin;
    }

    /**
     * @param array $viewdFiles
     */
    public function setViewdFiles($viewdFiles)
    {
        $this->viewdFiles = $viewdFiles;
    }

    /**
     * @return array
     */
    public function getViewdFiles()
    {
        return $this->viewdFiles;
    }


}
