<?php

namespace Application\Service;

use Zend\Crypt\Password\Bcrypt;
use AmaUsers\Entity\User;

class UserService
{

    static function verifyUser(User $user, $passwordGiven)
    {

        $bcrypt = new Bcrypt();
        if ( $bcrypt->verify($passwordGiven, $user->getPassword()) && $user->getState() == 1 ) {
            return true;
        }

        $tempPassword = $user->getTempPassword();
        if ( !empty($tempPassword) ) {
            if ( $bcrypt->verify($passwordGiven, $user->getTempPassword()) && $user->getState() == 1 ) {
                return true;
            }
        }

        return false;
    }
}