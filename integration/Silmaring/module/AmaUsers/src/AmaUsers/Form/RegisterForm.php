<?php
namespace AmaUsers\Form;

use Zend\Form\Form;

class RegisterForm extends Form
{
    public function __construct()
    {
        parent::__construct('registerForm');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'first_name',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => _('First name'),
                'class' => 'form-control',
                'autocomplete' => false
            ),
            'options' => array(
                'label' => ' ',
            ),
        ));

        $this->add(array(
            'name' => 'lastname',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => _('Lastname'),
                'class' => 'form-control',
                'autocomplete' => false
            ),
            'options' => array(
                'label' => ' ',
            ),
        ));


        $this->add(array(
            'name' => 'email',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => _('E-mail'),
                'class' => 'form-control',
                'autocomplete' => false
            ),
            'options' => array(
                'label' => ' ',
            ),
        ));

        $this->add(array(
            'name' => 'role',
            'type' => 'Select',
            'attributes' => array(
                'placeholder' => _('Choose role'),
                'class' => 'form-control',
                'autocomplete' => false,
            ),
            'options' => array(
                'label' => ' ',
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
                'type'  => 'button',
                'class' => 'btn btn-lg btn-primary btn-block',
            ),
            'options' => array(
                'label' => ' '
            )
        ));
    }
}
