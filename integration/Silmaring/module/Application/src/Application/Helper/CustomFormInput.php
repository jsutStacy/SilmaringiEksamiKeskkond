<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Helper;

use Zend\Form\ElementInterface;
use Zend\Form\Exception;
use Zend\Form\View\Helper\AbstractHelper;

class CustomFormInput extends AbstractHelper
{
    /**
     * Attributes valid for the input tag
     *
     * @var array
     */
    protected $validTagAttributes = array(
        'name'           => true,
        'accept'         => true,
        'alt'            => true,
        'autocomplete'   => true,
        'autofocus'      => true,
        'checked'        => true,
        'dirname'        => true,
        'disabled'       => true,
        'form'           => true,
        'formaction'     => true,
        'formenctype'    => true,
        'formmethod'     => true,
        'formnovalidate' => true,
        'formtarget'     => true,
        'height'         => true,
        'list'           => true,
        'max'            => true,
        'maxlength'      => true,
        'min'            => true,
        'multiple'       => true,
        'pattern'        => true,
        'placeholder'    => true,
        'readonly'       => true,
        'required'       => true,
        'size'           => true,
        'src'            => true,
        'step'           => true,
        'type'           => true,
        'value'          => true,
        'width'          => true,
    );

    /**
     * Valid values for the input type
     *
     * @var array
     */
    protected $validTypes = array(
        'text'           => true,
        'button'         => true,
        'checkbox'       => true,
        'file'           => true,
        'hidden'         => true,
        'image'          => true,
        'password'       => true,
        'radio'          => true,
        'reset'          => true,
        'select'         => true,
        'submit'         => true,
        'color'          => true,
        'date'           => true,
        'datetime'       => true,
        'datetime-local' => true,
        'email'          => true,
        'month'          => true,
        'number'         => true,
        'range'          => true,
        'search'         => true,
        'tel'            => true,
        'time'           => true,
        'url'            => true,
        'week'           => true,
    );

    /**
     * Invoke helper as functor
     *
     * Proxies to {@link render()}.
     *
     * @param  ElementInterface|null $element
     * @param int $qnr
     * @param string $anr
     * @return string|FormInput
     */
    public function __invoke(ElementInterface $element = null, $qnr = 0, $anr = '')
    {
        if (!$element) {
            return $this;
        }

        $element = $this->fixElement($this->render($element), $qnr, $anr);
        return $element;
    }

    private function fixElement($element, $qnr, $anr = '')
    {
        $element = str_replace("question_element&#x5B;0&#x5D;&#x5B;","question_element&#x5B;".$qnr."&#x5D;&#x5B;", $element);
        if($anr!='') {
            //$element = str_replace("answer_option_element&#x5D;&#x5B;0&#x5D;","answer_option_element&#x5D;&#x5B;".$anr."&#x5D;", $element);
            //$element = str_replace("answer_option_element&#x5D;&#x5B;1&#x5D;","answer_option_element&#x5D;&#x5B;".$anr."&#x5D;", $element);
            $element = str_replace("answer_option_element&#x5D;&#x5B;2&#x5D;","answer_option_element&#x5D;&#x5B;".$anr."&#x5D;", $element);
        }
        return $element;
    }

    /**
     * Render a form <input> element from the provided $element
     *
     * @param  ElementInterface $element
     * @throws Exception\DomainException
     * @return string
     */
    public function render(ElementInterface $element)
    {
        $name = $element->getName();
        if ($name === null || $name === '') {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has an assigned name; none discovered',
                __METHOD__
            ));
        }

        $attributes          = $element->getAttributes();
        $attributes['name']  = $name;
        $attributes['type']  = $this->getType($element);
        $attributes['value'] = $element->getValue();

        return sprintf(
            '<input %s%s',
            $this->createAttributesString($attributes),
            $this->getInlineClosingBracket()
        );
    }

    /**
     * Determine input type to use
     *
     * @param  ElementInterface $element
     * @return string
     */
    protected function getType(ElementInterface $element)
    {
        $type = $element->getAttribute('type');
        if (empty($type)) {
            return 'text';
        }

        $type = strtolower($type);
        if (!isset($this->validTypes[$type])) {
            return 'text';
        }

        return $type;
    }
}
