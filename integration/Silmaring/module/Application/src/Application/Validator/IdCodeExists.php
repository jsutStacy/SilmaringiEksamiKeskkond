<?php
namespace Application\Validator;

use CsnUser\Entity\User;
use Zend\Validator\AbstractValidator;

class IdCodeExists extends AbstractValidator
{
    const NOT_VALID = 'id_code_exists';
    
    protected $messageTemplates = array(
        self::NOT_VALID => "Selline isikukood eksisteerib juba!"
    );
    
    protected $em;

    public function __construct($options = null)
    {
        parent::__construct($options);
        
        if ($options && is_array($options) && array_key_exists('entityManager', $options)){
            $this->setEntityManager($options['entityManager']);
        }       
    }
    
    public function isValid($value)
    {
        
       
        $this->setValue($value);

        $isValid = true;
        
       if ( !$this->validate($value) ) {
         $this->error(self::NOT_VALID);
         $isValid = false;
       }
       
        if ( empty($value) ) $isValid = true;
        
        return $isValid;
    }
    
    public function validate($value)
    {
        $user = new User();
        $idCode = $user->hashIt($value);
        $exists = $this->getEntityManager()->getRepository('CsnUser\Entity\User')->findOneBy(array('idCodeHash' => $idCode));
        if ($exists) return false;
        else return true;
    }
    
    public function setEntityManager($em)
    {
        $this->em = $em;
        
        return $this;
    }
    
    public function getEntityManager()
    {
        return $this->em;
    }
}

?>