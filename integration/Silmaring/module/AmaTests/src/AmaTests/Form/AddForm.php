<?php
namespace AmaTests\Form;

use Application\Form\QuestionFieldset;
use Zend\Form\Form;

class AddForm extends Form
{
    public function __construct($em)
    {
        parent::__construct('addForm');

        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'name',
            'attributes' => array(
                'type'  => 'text',
                'class' => 'form-control input-lg',
                'autocomplete' => false,
                'id' => 'name',
                'placeholder' => _('TEST NAME:')
            ),
            'options' => array(
                'label' => ' ',
                'label_attributes' => array(
                    'class' => 'col-sm-2 control-label sr-only'
                )
            ),
        ));

        $this->add(array(
            'name' => 'description',
            'attributes' => array(
                'type'  => 'textarea',
                'class' => 'form-control input-lg',
                'autocomplete' => false,
                'id' => 'name',
                'placeholder' => _('Description:')
            ),
            'options' => array(
                'label' => ' ',
                'label_attributes' => array(
                    'class' => 'col-sm-2 control-label sr-only'
                )
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Collection',
            'name' => 'question_element',
            'options' => array(
                'count' => 1,
                'should_create_template' => true,
                'allow_add' => true,
                'allow_remove' => true,
                'template_placeholder' => '__question_element__',
                'target_element' => new QuestionFieldset($em)
            )
        ));


        $this->add(array(
            'name' => 'submit',
            'type' => 'Zend\Form\Element\Button',
            'attributes' => array(
                'type'  => 'button',
                'class' => 'btn btn-primary btn-lg pull-right',
            ),
            'options' => array(
                'label' => ' '
            )
        ));
    }
}
