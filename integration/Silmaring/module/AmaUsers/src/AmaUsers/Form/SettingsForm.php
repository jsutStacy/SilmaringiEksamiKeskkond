<?php
namespace AmaUsers\Form;

use Zend\Form\Form;

class SettingsForm extends Form
{
    public function __construct()
    {
        parent::__construct('settings');
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
            'name' => 'email',
            'attributes' => array(
                'type' => 'text',
                'placeholder' => _('E-mail address'),
                'class' => 'form-control',
                'autocomplete' => false
            ),
            'options' => array(
                'label' => ' ',
            ),
        ));

        $this->add(array(
            'name' => 'currentPassword',
            'attributes' => array(
                'type' => 'password',
                'placeholder' => _('Current password'),
                'class' => 'form-control',
                'autocomplete' => false
            ),
            'options' => array(
                'label' => ' '
            ),
        ));

        $this->add(array(
            'name' => 'newPassword',
            'attributes' => array(
                'type' => 'password',
                'placeholder' => _('New password'),
                'class' => 'form-control',
                'autocomplete' => false
            ),
            'options' => array(
                'label' => ' '
            ),
        ));

        $this->add(array(
            'name' => 'newPasswordConfirm',
            'attributes' => array(
                'type' => 'password',
                'placeholder' => _('New password confirm'),
                'class' => 'form-control',
                'autocomplete' => false
            ),
            'options' => array(
                'label' => ' '
            ),
        ));

        $this->add(array(
            'name' => 'image',
            'attributes' => array(
                'type' => 'Zend\Form\Element\File',
                'placeholder' => '',
                'class' => '',
                'autocomplete' => false
            ),
            'options' => array(
                'label' => _('Choose image ')
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
            'name' => 'state',
            'type' => 'Zend\Form\Element\Checkbox',
            'attributes' => array(
                'checked_value' => '1',
                'unchecked_value' => '0',
                'class' => 'form-control pull-left',
                'autocomplete' => false,
                'id' => 'status',
                'style' => 'width:20px; margin-top:-6px; margin-left:15px;'
            ),
            'options' => array(
                'label' => _('Active'),
                'label_attributes' => array(
                    'class' => 'control-label'
                )
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

        /*$this->add(array(
            'type' => 'csrf',
            'name' => 'csrf',
            'attributes' => array(
                'type' => 'hidden',
            ),
            'options' => array(
                'label' => ' ',
            ),
        ));*/

        $this->add(array(
            'name' => 'submit',
            'type' => 'Zend\Form\Element\Button',
            'attributes' => array(
                'type' => 'submit',
                'class' => 'btn btn-primary',
            ),
            'options' => array(
                'label' => ' '
            )
        ));
    }
}
