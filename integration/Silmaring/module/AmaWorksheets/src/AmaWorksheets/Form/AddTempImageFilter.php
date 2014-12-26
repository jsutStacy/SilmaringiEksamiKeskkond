<?php

namespace AmaWorksheets\Form;

use Zend\InputFilter\InputFilter;

class AddTempImageFilter extends InputFilter
{

    public function __construct($sm)
    {
        $config = $sm->get('Config');

        $this->add(array(
            'name'     => 'image',
            'required' => true,
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
    }
}

