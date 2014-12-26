<?php
namespace AmaCategories\Form;

use Zend\Form\Form;

class ImportForm extends Form
{
    public function __construct()
    {
        parent::__construct('importForm');

        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'file',
            'type' => 'Zend\Form\Element\File',
            'attributes' => array(
                'placeholder' => '',
                'class' => '',
                'autocomplete' => false
            ),
            'options' => array(
                'label' => _('File *'),
                'label_attributes' => array(
                    'class' => 'col-sm-2 control-label'
                )
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'type' => 'Zend\Form\Element\Button',
            'attributes' => array(
                'type'  => 'submit',
                'class' => 'btn btn-primary btn-lg pull-right',
            ),
            'options' => array(
                'label' => ' '
            )
        ));
    }
}
