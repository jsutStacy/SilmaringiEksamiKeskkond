<?php

namespace Application\Form;

use AmaTests\Entity\Answer;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Application\Filter\EscapeHtml;

class AnswerOptionFieldset extends Fieldset implements inputFilterProviderInterface
{

    protected $em;

    public function __construct($em)
    {
        parent::__construct('answer_option');

        $this->em = $em;
        $this->setHydrator(new DoctrineHydrator($this->em, 'AmaWorksheets\Entity\Answer'))->setObject(new Answer());

        $this->add(array(
            'name' => 'rightAnswer',
            'type' => 'Zend\Form\Element\Radio',
            'attributes' => array(
                'class' => '',
                'autocomplete' => false,
                'id' => 'rightAnswer'
            ),
            'options' => array(
                'label' => _('Right answer'),
                'label_attributes' => array(
                    'class' => 'col-sm-2 control-label sr-only',
                    'for' => 'rightAnswer'
                ),
                'value_options' => array(
                    '1' => '',
                ),
            ),
        ));

        $this->add(array(
            'name' => 'rightRange',
            'type' => 'Zend\Form\Element\Radio',
            'attributes' => array(
                'class' => '',
                'autocomplete' => false,
                'id' => 'rightRange'
            ),
            'options' => array(
                'label' => _('Right range'),
                'label_attributes' => array(
                    'class' => 'col-sm-2 control-label sr-only',
                    'for' => 'rightRange'
                ),
                'value_options' => array(
                    '1' => '',
                ),
            ),
        ));

        $this->add(array(
            'name' => 'option',
            'attributes' => array(
                'type' => 'text',
                'class' => 'form-control',
                'autocomplete' => false,
                'id' => 'name',
                'placeholder' => _('Option')
            ),
            'options' => array(
                'label' => ' ',
                'label_attributes' => array(
                    'class' => 'sr-only'
                )
            ),
        ));

        $this->add(array(
            'name' => 'optionTwo',
            'attributes' => array(
                'type' => 'text',
                'class' => 'form-control',
                'autocomplete' => false,
                'id' => 'name',
                'placeholder' => 'Option'
            ),
            'options' => array(
                'label' => ' ',
                'label_attributes' => array(
                    'class' => 'sr-only'
                )
            ),
        ));
    }

    public function getInputFilterSpecification()
    {
        return array(
            'rightRange' => array(
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                    new EscapeHtml()
                )
            ),
            'rightAnswer' => array(
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                    new EscapeHtml()
                )
            ),
            'option' => array(
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                    new EscapeHtml()
                )
            ),
            'optionTwo' => array(
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                    new EscapeHtml()
                )
            )
        );
    }
}