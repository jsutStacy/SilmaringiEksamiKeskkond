<?php
namespace AmaCategories\Form;

use Zend\Form\Form;

class AddForm extends Form
{
    public function __construct()
    {
        parent::__construct('addForm');
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
            'name' => 'order',
            'attributes' => array(
                'type'  => 'text',
                'class' => 'form-control',
                'autocomplete' => false,
                'id' => 'name'
            ),
            'options' => array(
                'label' => _('Order'),
                'label_attributes' => array(
                    'class' => 'col-sm-2 control-label'
                )
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
