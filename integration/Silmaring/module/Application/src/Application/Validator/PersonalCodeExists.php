<?php
namespace Application\Validator;

use Application\Service\EncryptDecryptService;
use Zend\Validator\AbstractValidator;

class PersonalCodeExists extends AbstractValidator
{
    const NOT_VALID = 'id_code_exists';
    
    protected $messageTemplates = array(
        self::NOT_VALID => "This personal code exists allready"
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
        $encrypt = new EncryptDecryptService();
        $personalCodeHash = $encrypt->hashIt($value);
        $exists = $this->getEntityManager()->getRepository('AmaUsers\Entity\User')->findOneBy(array('personalCodeHash' => $personalCodeHash));
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