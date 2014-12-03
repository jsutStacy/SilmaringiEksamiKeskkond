<?php

namespace EksamiKeskkond\Filter;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class EmailFilter extends InputFilter {

	public function __construct($sm) {

		$this->add(array(
				'name' => 'id',
				'required' => true,
				'filters' => array(
						array('name' => 'Int'),
				),
		));

		$this->add(array(
			'name' => 'title',
			'required' => true,
			'filters' => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
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