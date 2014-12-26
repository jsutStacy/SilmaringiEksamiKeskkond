<?php

namespace AmaMaterials\Form;

use Application\Filter\EscapeHtml;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;

class AddFilter extends InputFilter
{

    public function __construct($sm)
    {
        $config = $sm->get('Config');

        $this->add(array(
            'name' => 'name',
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
                            NotEmpty::IS_EMPTY => _('Please insert name!'),
                        ),
                    ),
                ),
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min' => 0,
                        'max' => 255,
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'description',
            'required' => false,
            'filters' => array(
                new EscapeHtml()
            )
        ));

        $this->add(array(
            'name' => 'video',
            'required' => false,
            'filters' => array(
                new EscapeHtml()
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array(
                            NotEmpty::IS_EMPTY => _('Please insert video url!'),
                        ),
                    ),
                ),
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min' => 0,
                        'max' => 255,
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name'     => 'image',
            'required' => false,
            'validators' => array(
                array(
                    'name' => 'filemimetype',
                    'options' =>  array(
                        'mimeType' => $config['image_mime_types'],
                        'message'  => _('Wrong file type. Allowed ').$config['image_file_types'].'!'
                    ),
                ),
                array(
                    'name' => 'fileimagesize',
                    'options' => array(
                        'maxWidth' => $config['max_image_width'],#160
                        'maxHeight' => $config['max_image_height'], #215
                        'message'  => _('Wrong image size. Allowed width %maxwidth%px and height %maxheight%px!')
                    ),
                ),
                array(
                    'name' => 'fileextension',
                    'options' =>  array(
                        'extension' => $config['image_file_types'],
                        'message'  => _('Wrong file. Allowed ').$config['image_file_types'].'!'
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name'     => 'file',
            'required' => false,
            'validators' => array(
                array(
                    'name' => 'filemimetype',
                    'options' =>  array(
                        'mimeType' =>  $config['file_mime_types'],
                        'message'  => _('Wrong file type. Allowed '). $config['file_types'] .'!'
                    ),
                ),
                array(
                    'name' => 'fileextension',
                    'options' =>  array(
                        'extension' => $config['file_types'],
                        'message'  => _('Wrong file. Allowed ').$config['file_types'].'!'
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'status',
            'required' => false,
            'filters' => array(
                new EscapeHtml()
            )
        ));

    }
}