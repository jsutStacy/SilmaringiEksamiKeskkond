<?php
namespace AmaTests\Form;

use Zend\Form\Form;

class AddTempImageForm extends Form
{
    public function __construct()
    {
        parent::__construct('addTempImageForm');

        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'image',
            'type' => 'Zend\Form\Element\File',
            'attributes' => array(
                'placeholder' => '',
                'class' => '',
                'autocomplete' => false
            ),
            'options' => array(
                'label' => _('Image *'),
                'label_attributes' => array(
                    'class' => 'col-sm-2 control-label'
                )
            ),
        ));
    }
}
