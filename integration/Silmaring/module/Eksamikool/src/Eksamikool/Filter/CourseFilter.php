<?php

namespace Eksamikool\Filter;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class CourseFilter extends InputFilter {

	public function __construct($sm) {

		$this->add(array(
			'name' => 'id',
			'required' => true,
			'filters' => array(
				array('name' => 'Int'),
			),
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
			'name' => 'price',
			'required' => true,
			'validators' => array(
				array(
					'name' => 'Between',
					'options' => array(
						'min' => 0,
					),
				),
			),
		));

		$this->add(array(
			'name' => 'start_date',
			'required' => true,
			'filters' => array(
				array('name' => 'StripTags'),
				array('name' => 'StringTrim'),
			),
		));

		$this->add(array(
			'name' => 'end_date',
			'required' => true,
			'filters' => array(
				array('name' => 'StripTags'),
				array('name' => 'StringTrim'),
			),
			'validators' => array(
				array(
					'name' => 'Callback',
					'options' => array(
						'messages' => array(
								\Zend\Validator\Callback::INVALID_VALUE => 'Lõpukuupäev peaks olema hilisem kui alguskuupäev.',
						),
						'callback' => function($value, $context = array()) {
							$startDate = \DateTime::createFromFormat('Y-m-d', $context['start_date']);
							$endDate = \DateTime::createFromFormat('Y-m-d', $value);
							return $endDate >= $startDate;
						},
					),
				),
			),
		));

		$this->add(array(
			'name' => 'teacher_id',
			'required' => false,
		));

		$this->add(array(
			'name' => 'published',
			'required' => true,
		));

	}
}