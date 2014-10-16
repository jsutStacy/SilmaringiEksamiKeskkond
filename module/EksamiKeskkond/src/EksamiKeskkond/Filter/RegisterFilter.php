<?php

namespace EksamiKeskkond\Filter;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class RegisterFilter extends InputFilter {

	public function __construct($sm) {

		$this->add(array(
			'name' => 'firstname',
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
						'min' => '2',
						'max' => '50',
					),
				),
			),
		));

		$this->add(array(
			'name' => 'lastname',
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
						'min' => '2',
						'max' => '50',
					),
				),
			),
		));

		$this->add(array(
			'name' => 'email',
			'required' => true,
			'validators' => array(
				array(
					'name' => 'EmailAddress',
				),
				array(
					'name' => 'Zend\Validator\Db\NoRecordExists',
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

		$this->add(array(
			'name' => 'password_confirm',
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
				array(
					'name' => 'Identical',
					'options' => array(
						'token' => 'password',
					),
				),
			),
		));

	}
}