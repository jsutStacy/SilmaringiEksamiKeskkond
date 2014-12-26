<?php

namespace Application\Form;

use AmaTests\Entity\UserTestAnswer;
use Application\Filter\EscapeHtml;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Validator\Digits;

class UserQuestionFieldset extends Fieldset  implements inputFilterProviderInterface
{

    protected $em;

    public function __construct($em)
    {

        parent::__construct('user_question');

        $this->em = $em;
        $this->setHydrator(new DoctrineHydrator($this->em, 'AmaWorksheets\Entity\UserTestAnswer'))->setObject(new UserTestAnswer());

        $this->add(array(
            'name' => 'question',
            'attributes' => array(
                'type'  => 'hidden',
                'class' => 'form-control',
                'autocomplete' => false,
                'id' => '',
                'placeholder' => ''
            ),
            'options' => array(
                'label' => ' ',
                'label_attributes' => array(
                    'class' => 'col-sm-2 control-label sr-only'
                )
            ),
        ));

        $this->add(array(
            'name' => 'answerText',
            'attributes' => array(
                'type' => 'textarea',
                'class' => 'form-control',
                'autocomplete' => false,
                'id' => 'name',
                'placeholder' => _('Answer here:')
            ),
            'options' => array(
                'label' => ' ',
                'label_attributes' => array(
                    'class' => 'sr-only'
                )
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Collection',
            'name' => 'user_answer_element',
            'options' => array(
                'count' => 1,
                'should_create_template' => true,
                'allow_add' => true,
                'allow_remove' => true,
                'template_placeholder' => '__user_answer_element__',
                'target_element' => new UserAnswerFieldset($this->em)
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
                ),
                'validators' => array(
                    array(
                        'name' => 'Digits',
                        'options' => array(
                            'messages' => array(
                                Digits::NOT_DIGITS => _('Must be number!'),
                            ),
                        ),
                    ),
                ),
            ),
            'answerText' => array(
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                    new EscapeHtml()
                )
            ),
        );
    }
}