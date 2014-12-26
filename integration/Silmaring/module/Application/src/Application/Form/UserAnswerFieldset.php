<?php

namespace Application\Form;

use AmaTests\Entity\UserTestAnswer;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Application\Filter\EscapeHtml;
use Zend\Validator\Digits;

class UserAnswerFieldset extends Fieldset implements inputFilterProviderInterface
{

    protected $em;

    public function __construct($em)
    {
        parent::__construct('user_answer');

        $this->em = $em;
        $this->setHydrator(new DoctrineHydrator($this->em, 'AmaWorksheets\Entity\UserTestAnswer'))->setObject(new UserTestAnswer());

        $this->add(array(
            'name' => 'answer',
            'type' => 'Zend\Form\Element\Radio',
            'attributes' => array(
                'class' => '',
                'autocomplete' => false,
            ),
            'options' => array(
                'label' => ' ',
                'label_attributes' => array(
                    'class' => 'col-sm-2 control-label'
                ),
                'value_options' => array(
                    '1' => '',
                ),
            ),
        ));
    }

    public function getInputFilterSpecification()
    {
        return array(
            'answer' => array(
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
        );
    }
}