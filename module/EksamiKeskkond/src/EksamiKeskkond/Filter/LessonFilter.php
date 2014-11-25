<?php

namespace EksamiKeskkond\Filter;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class LessonFilter extends InputFilter {

	public function __construct($sm) {

		$this->add(array(
			'name' => 'id',
			'required' => true,
			'filters' => array(
				array('name' => 'Int'),
			),
		));

		$this->add(array(
				'name' => 'published',
				'required' => true,
		));

		$this->add(array(
			'name' => 'name',
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
						'min' => 1,
						'max' => 255,
					),
				),
			),
		));

		$this->add(array(
			'name' => 'content',
			'required' => true,
			'filters' => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
			),
		));

	}
}