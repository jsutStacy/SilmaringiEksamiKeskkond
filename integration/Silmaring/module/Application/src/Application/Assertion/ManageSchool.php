<?php
namespace Application\Assertion;

use Zend\Di\ServiceLocator;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ManageSchool implements AssertionInterface
{

    protected $sm;

    public function assert(Acl $acl,
                           RoleInterface $role = null,
                           ResourceInterface $resource = null,
                           $privilege = null) {

        //var_dump($this->sm);
        return false;
    }

    private function checkIfIsOwner($resource)
    {
        return false;
    }

    public function setServiceLocator($serviceLocator)
    {
        $this->sm = $serviceLocator;
    }
}