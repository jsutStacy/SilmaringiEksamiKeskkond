<?php
namespace AmaTests\Form;

use Application\Form\UserQuestionFieldset;
use Zend\Form\Form;

class SolveForm extends Form
{
    public function __construct($em)
    {
        parent::__construct('solveForm');

        $this->setAttribute('method', 'post');

        $this->add(array(
            'type' => 'Zend\Form\Element\Collection',
            'name' => 'user_question_element',
            'options' => array(
                'count' => 1,
                'should_create_template' => true,
                'allow_add' => true,
                'allow_remove' => true,
                'template_placeholder' => '__user_question_element__',
                'target_element' => new UserQuestionFieldset($em)
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
