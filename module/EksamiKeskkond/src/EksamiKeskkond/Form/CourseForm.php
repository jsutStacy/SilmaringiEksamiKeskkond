<?php
namespace EksamiKeskkond\Form;

use Zend\Form\Form;

class CourseForm extends Form {

	public function __construct($name = null) {

		// we want to ignore the name passed
		parent::__construct('EksamiKeskkond');

		$this->setAttribute('method', 'post');

		$this->add(array(
			'name' => 'id',
			'attributes' => array(
				'type' => 'hidden',
			),
		));

		$this->add(array(
			'name' => 'name',
			'attributes' => array(
				'type' => 'text',
			),
			'options' => array(
				'label' => 'Kursuse nimi',
			),
		));

		$this->add(array(
			'name' => 'price',
			'attributes' => array(
				'type' => 'number',
			),
			'options' => array(
				'label' => 'Kursuse hind',
			),
		));

		$this->add(array(
			'type' => 'Zend\Form\Element\Select',
			'name' => 'published',
			'options' => array(
				'label' => 'Kursuse nähtavus',
				'options' => array(
					'0' => 'Privaatne',
					'1' => 'Nähtav',
				),
			),
		));

		$this->add(array(
			'name' => 'submit',
			'attributes' => array(
				'type' => 'submit',
				'value' => 'Lisa',
				'id' => 'submitbutton',
			),
		));

	}
}