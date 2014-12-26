<?php
namespace Application\Service;

use BjyAuthorize\Provider\Identity\ProviderInterface;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMapping;

class AmaIdentityProviderService implements ProviderInterface
{
    protected $userService;
    protected $defaultRole = 'guest';

    public function __construct($em)
    {
        $this->em = $em;
    }

    public function getIdentityRoles()
    {
        $authService = $this->userService->getAuthService();


        if (!$authService->hasIdentity()) {
            // get default/guest role
            return $this->getDefaultRole();
        } else {
            // get roles associated with the logged in user
            $identity = $authService->getIdentity();
            if (is_object($identity)) {
                $id = $identity->getId();
            } else {
                $id = $identity['id'];
            }

            $rsm = new ResultSetMapping;
            $rsm->addEntityResult('AmaUsers\Entity\Role', 'r');
            $rsm->addScalarResult('roleId', 'id');

            $query = $this->em->createNativeQuery('SELECT roleId FROM user_role_linker INNER JOIN role ON role.id=user_role_linker.role_id WHERE user_id = ?', $rsm);
            $query->setParameter(1, $id);
            $getQuery = $query;
            $getQuery->useResultCache(true, 86400,  'UserRoles' . $id);
            try {
                $result =  $getQuery->getResult();
                $roles = array();
                foreach ($result as $row) {
                    $roles[] = $row['id'];
                }
                return $roles;
            } catch (NoResultException $e) {
                return array();
            }
        }
    }

    public function getUserService()
    {
        return $this->userService;
    }

    public function setUserService($userService)
    {
        $this->userService = $userService;
        return $this;
    }

    public function getDefaultRole()
    {
        return $this->defaultRole;
    }

    public function setDefaultRole($defaultRole)
    {
        $this->defaultRole = $defaultRole;
        return $this;
    }
}