<?php

namespace AmaUsers\Form;

use Application\Filter\EscapeHtml;
use Zend\InputFilter\InputFilter;
use Zend\Validator\EmailAddress;
use Zend\Validator\NotEmpty;
use Zend\Validator\Csrf;
use Zend\Validator\StringLength;

class SettingsFilter extends InputFilter
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
                            EmailAddress::INVALID_FORMAT => _('Email address format is invalid!')
                        )
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'personalCode',
            'required' => false,
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
                        'min' => 11,
                        'max' => 11,
                    ),
                ),
                /*array(
                    'name'=>'personalCodeExists'
                ),
                array(
                    'name'=>'personalCodeValidator'
                )*/
            ),
        ));

        $this->add(array(
            'name' => 'firstName',
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
                            NotEmpty::IS_EMPTY => _('Please insert first name!'),
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
            ),
        ));


        $this->add(array(
            'name' => 'lastname',
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
                            NotEmpty::IS_EMPTY => _('Please insert lastname!'),
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
            ),
        ));



        $this->add(array(
            'name' => 'currentPassword',
            'required' => false,
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
                            NotEmpty::IS_EMPTY => _('Current password field is empty!'),
                        ),
                    ),
                ),
            ),
        ));


        $this->add(array(
            'name' => 'newPassword',
            'required' => false,
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
                        'max' => 15,
                        'messages' => array(
                            StringLength::TOO_SHORT => _('New password is shorter than %min% characters!'),
                            StringLength::TOO_LONG => _('New password is longer than %max% characters!')
                        ),
                    ),
                ),
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array(
                            NotEmpty::IS_EMPTY => _('New password field is empty!'),
                        ),
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'newPasswordConfirm',
            'required' => false,
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
                        'max' => 15,
                        'messages' => array(
                            StringLength::TOO_SHORT => _('New password confirm is shorter than %min% characters!'),
                            StringLength::TOO_LONG => _('New password confirm is longer than %max% characters!')
                        ),
                    ),
                ),
                array(
                    'name' => 'Identical',
                    'options' => array(
                        'token' => 'newPassword',
                        'message' => 'Passwords do not match!'
                    ),
                ),
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array(
                            NotEmpty::IS_EMPTY => _('New password confirm field is empty!'),
                        ),
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name'     => 'image',
            'required' => false,
            'validators' => array(
                array(
                    'name' => 'filemimetype',
                    'options' =>  array(
                        'mimeType' => 'image/jpg,image/jpeg,image/gif,image/png',
                        'message'  => _('Wrong file. Allowed jpg,jpeg,png,gif!')
                    ),
                ),
                array(
                    'name' => 'fileimagesize',
                    'options' => array(
                        'maxWidth' => 3000,#160
                        'maxHeight' => 3000, #215
                        'message'  => _('Wrong image size. Allowed width %maxwidth%px and height %maxheight%px!')
                    ),
                ),
                array(
                    'name' => 'fileextension',
                    'options' =>  array(
                        'extension' => 'jpg,jpeg,gif,png',
                        'message'  => _('Wrong file. Allowed jpg,jpeg,png,gif!')
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'state',
            'required' => false,
            'filters' => array(
                new EscapeHtml()
            )
        ));


        $this->add(array(
            'name' => 'role',
            'required' => false,
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
                            NotEmpty::IS_EMPTY => _('Please choose role!'),
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
            ),
        ));

        /*$this->add(array(
            'required' => false,
            'name' => 'csrf',
            'validators' => array(
                array(
                    'name' => 'Csrf',
                    'options' => array(
                        'messages' => array(
                            Csrf::NOT_SAME => 'Form session has timed out. Please reload page!',
                        )
                    )
                )
            )
        ));*/
    }
}