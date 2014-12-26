<?php

namespace Eksamikool\Filter;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class HomeworkFilter extends InputFilter {

	public function __construct($sm) {

		$this->add(array(
			'name' => 'id',
			'required' => true,
			'filters' => array(
				array('name' => 'Int'),
			),
		));

		$this->add(array(
			'name' => 'subsubject_id',
			'required' => false,
		));

		$this->add(array(
			'name' => 'description',
			'required' => true,
			'filters' => array(
				array('name' => 'StripTags'),
				array('name' => 'StringTrim'),
			),
		));

		$this->add(array(
			'name' => 'fileupload',
			'required' => false,
		));
	}
}