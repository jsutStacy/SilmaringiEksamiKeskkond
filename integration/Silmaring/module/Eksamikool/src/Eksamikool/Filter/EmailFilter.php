<?php

namespace Eksamikool\Filter;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class EmailFilter extends InputFilter {

	public function __construct($sm) {

		$this->add(array(
			'name' => 'subject',
			'required' => true,
			'filters' => array(
				array('name' => 'StripTags'),
				array('name' => 'StringTrim'),
			),
		));

		$this->add(array(
			'name' => 'body',
			'required' => true,
			'filters' => array(
				array('name' => 'StripTags'),
				array('name' => 'StringTrim'),
			),
		));
	}
}