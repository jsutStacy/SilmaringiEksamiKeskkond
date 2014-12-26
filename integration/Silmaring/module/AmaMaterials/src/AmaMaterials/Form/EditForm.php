<?php
namespace AmaMaterials\Form;

use Zend\Form\Form;

class EditForm extends Form
{
    public function __construct()
    {
        parent::__construct('editForm');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'name',
            'attributes' => array(
                'type'  => 'text',
                'class' => 'form-control',
                'autocomplete' => false,
                'id' => 'name'
            ),
            'options' => array(
                'label' => _('Name *'),
                'label_attributes' => array(
                    'class' => 'col-sm-2 control-label'
                )
            ),
        ));

        $this->add(array(
            'name' => 'description',
            'attributes' => array(
                'type'  => 'text',
                'class' => 'form-control',
                'autocomplete' => false,
                'id' => 'name'
            ),
            'options' => array(
                'label' => _('Description'),
                'label_attributes' => array(
                    'class' => 'col-sm-2 control-label'
                )
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
                'label' => _('Image *')
            ),
        ));

        $this->add(array(
            'name' => 'file',
            'attributes' => array(
                'type' => 'Zend\Form\Element\File',
                'placeholder' => '',
                'class' => '',
                'autocomplete' => false
            ),
            'options' => array(
                'label' => _('File *')
            ),
        ));


        $this->add(array(
            'name' => 'status',
            'type' => 'Zend\Form\Element\Checkbox',
            'attributes' => array(
                'checked_value' => '1',
                'unchecked_value' => '0',
                'class' => 'form-control',
                'autocomplete' => false,
                'id' => 'status'
            ),
            'options' => array(
                'label' => _('Public'),
                'label_attributes' => array(
                    'class' => 'col-sm-2 control-label'
                )
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
