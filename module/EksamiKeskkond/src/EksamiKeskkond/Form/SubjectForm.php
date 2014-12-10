<?php
namespace EksamiKeskkond\Form;

use Zend\Form\Form;

class SubjectForm extends Form {

	public function __construct($name = null) {

		// we want to ignore the name passed
		parent::__construct('EksamiKeskkond');

		$this->setAttributes(array(
			'method' => 'post',
			'class' => 'form-horizontal'
		));

		$this->add(array(
			'name' => 'id',
			'attributes' => array(
				'type' => 'hidden',
			),
		));

		$this->add(array(
			'name' => 'course_id',
			'attributes' => array(
				'type' => 'hidden',
			),
		));

		$this->add(array(
			'name' => 'name',
			'attributes' => array(
				'type' => 'text',
				'class' => 'form-control',
				'placeholder' => 'Teema nimi',
			),
		));

		$this->add(array(
			'name' => 'description',
			'attributes' => array(
				'type' => 'textarea',
				'class' => 'form-control',
			),
			'options' => array(
				'label' => 'Teema kirjeldus',
			),
		));


		$this->add(array(
			'name' => 'submit',
			'attributes' => array(
				'type' => 'submit',
				'value' => 'Lisa',
				'id' => 'submitbutton',
				'class' => 'btn btn-default',
			),
		));

	}
}