<?php
namespace AmaUsers\Form;

use Zend\Form\Form;

class EditTeacher extends Form
{
    public function __construct()
    {
        parent::__construct('editTeacher');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'firstName',
            'attributes' => array(
                'type' => 'text',
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
                'type' => 'text',
                'placeholder' => _('Lastname'),
                'class' => 'form-control',
                'autocomplete' => false
            ),
            'options' => array(
                'label' => ' ',
            ),
        ));

        $this->add(array(
            'name' => 'personalCode',
            'attributes' => array(
                'type' => 'text',
                'placeholder' => _('Personal code'),
                'class' => 'form-control',
                'autocomplete' => false
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
