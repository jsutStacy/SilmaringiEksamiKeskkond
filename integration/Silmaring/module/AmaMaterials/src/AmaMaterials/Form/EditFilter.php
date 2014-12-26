<?php

namespace AmaMaterials\Form;

use Application\Filter\EscapeHtml;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;

class EditFilter extends InputFilter
{

    const FILE_TYPES = 'doc,docx,xls,xlsx,pdf,txt,odt,rtf,ppt,odp,ods';
    const IMAGE_FILE_TYPES = 'jpg,jpeg,png,gif';
    const IMAGE_MIME_TYPES = 'image/jpg,image/jpeg,image/gif,image/png';
    const FILE_MIME_TYPES  = 'application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/pdf,text/plain,application/vnd.oasis.opendocument.text,application/rtf,application/vnd.ms-powerpoint,application/vnd.oasis.opendocument.presentation,application/vnd.oasis.opendocument.spreadsheet';

    public function __construct($sm)
    {
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
            'name'     => 'image',
            'required' => false,
            'validators' => array(
                array(
                    'name' => 'filemimetype',
                    'options' =>  array(
                        'mimeType' => self::IMAGE_MIME_TYPES,
                        'message'  => _('Wrong file. Allowed ').self::IMAGE_FILE_TYPES.'!'
                    ),
                ),
                array(
                    'name' => 'fileimagesize',
                    'options' => array(
                        'maxWidth' => 3000,#160
                        'maxHeight' => 3000, #215
                        'message'  => _('Wrong image size. Allowed width %maxwidth%px and height %maxheight%px!')
                    ),
                ),
                array(
                    'name' => 'fileextension',
                    'options' =>  array(
                        'extension' => self::IMAGE_FILE_TYPES,
                        'message'  => _('Wrong file. Allowed ').self::IMAGE_FILE_TYPES.'!'
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
                        'mimeType' => self::FILE_MIME_TYPES,
                        'message'  => _('Wrong file. Allowed ').self::FILE_TYPES.'!'
                    ),
                ),
                array(
                    'name' => 'fileextension',
                    'options' =>  array(
                        'extension' => self::FILE_TYPES,
                        'message'  => _('Wrong file. Allowed ').self::FILE_TYPES.'!'
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