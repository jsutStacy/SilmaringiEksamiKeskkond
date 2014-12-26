<?php

namespace AmaUsers\Form;

use Application\Filter\EscapeHtml;
use Zend\InputFilter\InputFilter;
use Zend\Validator\EmailAddress;
use Zend\Validator\NotEmpty;
use Zend\Validator\Csrf;

class ForgottenPasswordFilter extends InputFilter
{
    public function __construct($sm)
    {

        $this->add(array(
            'name' => 'email',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
                new EscapeHtml()
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array(
                            NotEmpty::IS_EMPTY => _('Please insert email!'),
                        ),
                    ),
                ),
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min' => 0,
                        'max' => 255,
                    ),
                ),
                array(
                    'name' => 'EmailAddress',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min' => 5,
                        'max' => 255,
                        'messages' => array(
                            EmailAddress::INVALID_FORMAT => _('Email address format is invalid')
                        )
                    ),
                ),
                array(
                    'name' => 'DoctrineModule\Validator\ObjectExists',
                    'options' => array(
                        'object_repository' => $sm->get('doctrine.entitymanager.orm_default')->getRepository('AmaUsers\Entity\User'),
                        'fields' => 'email',
                        'message' => _('This email address does not exist in our system!')
                    ),
                ),
            ),
        ));

        $this->add(array(
            'required' => true,
            'name' => 'csrf',
            'validators' => array(
                array(
                    'name' => 'Csrf',
                    'options' => array(
                        'messages' => array(
                            Csrf::NOT_SAME => _('Form session has timed out. Please reload page!'),
                        )
                    )
                )
            )
        ));
    }
}