<?php

namespace AmaUsers\Form;

use Application\Filter\EscapeHtml;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Csrf;
use Zend\Validator\EmailAddress;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class LoginFilter extends InputFilter
{
    public function __construct($sm)
    {
        // self::__construct(); // parnt::__construct(); - trows and error
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
                            NotEmpty::IS_EMPTY => _('Email field cannot be empty!'),
                        ),
                    ),
                ),
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min' => 0,
                        'max' => 255,
                        'messages' => array(
                            StringLength::INVALID => _('Wrong email or password!')
                        )
                    ),
                ),
                array(
                    'name' => 'EmailAddress',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min' => 5,
                        'max' => 255,
                        'messages' => array(
                            EmailAddress::INVALID_FORMAT => _('Wrong email format!')
                        )
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'password',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
                new EscapeHtml()
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min' => 6,
                        'max' => 150,
                        'message' => _('Wrong email or password!')
                    ),
                ),
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array(
                            NotEmpty::IS_EMPTY => _('Password field cannot be empty!'),
                        ),
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
                            Csrf::NOT_SAME =>  _('Form session has timed out. Please reload page!'),
                        )
                    )
                )
            )
        ));
    }
}