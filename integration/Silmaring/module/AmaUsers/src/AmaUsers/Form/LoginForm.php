<?php
namespace AmaUsers\Form;

use Zend\Form\Form;

class LoginForm extends Form
{
    public function __construct()
    {
        parent::__construct('login');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'email',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => _('Email address'),
                'class' => 'form-control',
                'autocomplete' => false
            ),
            'options' => array(
                'label' => ' ',
            ),
        ));

        $this->add(array(
            'name' => 'password',
            'attributes' => array(
                'type'  => 'password',
                'placeholder' => _('Password'),
                'class' => 'form-control'
            ),
            'options' => array(
                'label' => ' ',
            ),
        ));

        $this->add(array(
            'name' => 'rememberme',
            'type' => 'checkbox',
            'options' => array(
                'label' => _('Remember me?'),
                'label_attributes' => array(
                    'class' => 'checkbox'
                ),
            ),
        ));

        $this->add(array(
            'type' => 'csrf',
            'name' => 'csrf',
            'attributes' => array(
                'type'  => 'hidden',
            ),
            'options' => array(
                'label' => ' ',
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'type' => 'Zend\Form\Element\Button',
            'attributes' => array(
                'type'  => 'submit',
                'class' => 'btn btn-lg btn-primary btn-block',
            ),
            'options' => array(
                'label' => ' '
            )
        ));
    }
}
