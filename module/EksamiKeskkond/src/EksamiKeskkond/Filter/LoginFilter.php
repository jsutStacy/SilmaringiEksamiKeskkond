<?php

namespace EksamiKeskkond\Filter;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class LoginFilter extends InputFilter {

	public function __construct($sm) {

		$this->add(array(
			'name' => 'email',
			'required' => true,
			'validators' => array(
				array(
					'name' => 'EmailAddress',
				),
				array(
					'name' => 'Zend\Validator\Db\RecordExists',
					'options' => array(
						'table' => 'user',
						'field' => 'email',
						'adapter' => $sm->get('Zend\Db\Adapter\Adapter'),
					),
				),
			),
		));

		$this->add(array(
			'name' => 'password',
			'required' => true,
			'filters' => array(
				array('name' => 'StripTags'),
				array('name' => 'StringTrim'),
			),
			'validators' => array(
				array(
					'name' => 'StringLength',
					'options' => array(
						'encoding' => 'UTF-8',
						'min' => '6',
						'max' => '50',
					),
				),
			),
		));

	}
}