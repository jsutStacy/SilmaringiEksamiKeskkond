<?php

namespace Application\Form;

use AmaTests\Entity\Question;
use Application\Filter\EscapeHtml;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Validator\Digits;
use Zend\Validator\NotEmpty;

class QuestionFieldset extends Fieldset  implements inputFilterProviderInterface
{

    protected $em;

    public function __construct($em)
    {

        parent::__construct('question');

        $this->em = $em;
        $this->setHydrator(new DoctrineHydrator($this->em, 'AmaWorksheets\Entity\Question'))->setObject(new Question());

        $this->add(array(
            'name' => 'question',
            'attributes' => array(
                'type'  => 'text',
                'class' => 'form-control question_text',
                'autocomplete' => false,
                'id' => 'name',
                'placeholder' => _('Question'),
                'onkeyup' => 'silmaringTest.updateQuestionText($(this));'
            ),
            'options' => array(
                'label' => ' ',
                'label_attributes' => array(
                    'class' => 'col-sm-2 control-label sr-only'
                )
            ),
        ));

        $this->add(array(
            'name' => 'mustContainWords',
            'type' => 'Zend\Form\Element\Checkbox',
            'attributes' => array(
                'checked_value' => '1',
                'unchecked_value' => '0',
                'class' => '',
                'autocomplete' => false,
                'id' => 'mustContainWords'
            ),
            'options' => array(
                'label' => _('Must contain words: (example: word1,word2,word3 etc)'),
                'label_attributes' => array(
                    'class' => 'col-sm-2 control-label',
                    'for' => 'mustContainWords'
                )
            ),
        ));

        $this->add(array(
            'name' => 'words',
            'attributes' => array(
                'type'  => 'text',
                'class' => 'form-control',
                'autocomplete' => false,
                'id' => 'name',
                'placeholder' => _('Enter words')
            ),
            'options' => array(
                'label' => ' ',
                'label_attributes' => array(
                    'class' => 'col-sm-2 control-label sr-only'
                )
            ),
        ));

        $this->add(array(
            'name' => 'points',
            'attributes' => array(
                'type'  => 'text',
                'class' => 'form-control question_points',
                'autocomplete' => false,
                'id' => 'name',
                'placeholder' => '',
                'onkeyup' => 'silmaringTest.updateQuestionPoints($(this));'
            ),
            'options' => array(
                'label' => _('points *'),
                'label_attributes' => array(
                    'class' => 'form_inline_txt'
                )
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Collection',
            'name' => 'answer_option_element',
            'options' => array(
                'count' => 3,
                'should_create_template' => true,
                'allow_add' => true,
                'allow_remove' => true,
                'template_placeholder' => '__answer_option_element__',
                'target_element' => new AnswerOptionFieldset($this->em)
            )
        ));

    }

    public function getInputFilterSpecification()
    {
        return array(
            'question' => array(
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                    new EscapeHtml()
                )
            ),
            'mustContainWords' => array(
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                    new EscapeHtml()
                )
            ),
            'words' => array(
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                    new EscapeHtml()
                )
            ),
            'points' => array(
                'required' => true,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                    new EscapeHtml()
                ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => _('Please insert number of points!'),
                            ),
                        ),
                    ),
                    array(
                        'name' => 'Between',
                        'options' => array(
                            'min' => 1,
                            'max' => 10,
                            'messages' => array(
                                'notBetween' => _('Points must be between %min% and %max%'),
                            ),
                        ),
                    ),
                    array(
                        'name' => 'Digits',
                        'options' => array(
                            'messages' => array(
                                Digits::NOT_DIGITS => 'Please insert number!',
                            ),
                        ),
                    ),
                ),
            )
        );
    }
}