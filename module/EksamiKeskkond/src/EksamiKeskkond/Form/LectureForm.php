<?php
namespace EksamiKeskkond\Form;

use Zend\Form\Form;

class LectureForm extends Form {

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
				'name' => 'subject_id',
				'attributes' => array(
						'type' => 'hidden',
				),
		));

		$this->add(array(
			'name' => 'name',
			'attributes' => array(
				'type' => 'text',
				'class' => 'form-control',
			),
			'options' => array(
				'label' => 'Tunni nimi',
			),
		));

		$this->add(array(
			'name' => 'content',
			'attributes' => array(
				'type' => 'textarea',
				'class' => 'form-control',
			),
			'options' => array(
				'label' => 'Tunni sisu',
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