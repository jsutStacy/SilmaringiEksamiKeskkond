<?php

namespace AmaCategories\Form;

use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;

class ImportFilter extends InputFilter
{

    public function __construct($sm)
    {
        $this->add(array(
            'name'     => 'file',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'filemimetype',
                    'options' =>  array(
                        'mimeType' =>  'application/vnd.ms-excel,application/vnd.ms-office,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'message'  => _('Wrong file type. Allowed ') .'xls,xlsx!'
                    ),
                ),
                array(
                    'name' => 'fileextension',
                    'options' =>  array(
                        'extension' => 'xls,xlsx',
                        'message'  => _('Wrong file. Allowed ').'xls,xlsx!'
                    ),
                ),
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array(
                            NotEmpty::IS_EMPTY => _('Please choose file'),
                        ),
                    ),
                ),
            ),
        ));

    }
}