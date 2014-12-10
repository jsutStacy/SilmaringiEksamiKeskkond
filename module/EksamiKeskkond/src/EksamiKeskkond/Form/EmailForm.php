<?php
namespace EksamiKeskkond\Form;

use Zend\Form\Form;

class EmailForm extends Form {

	public function __construct($name = null) {

		// we want to ignore the name passed
		parent::__construct('EksamiKeskkond');

		$this->setAttributes(array(
			'method' => 'post',
			'class' => 'form-horizontal'
		));

		$this->add(array(
			'name' => 'user_id',
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
			'name' => 'subject',
			'attributes' => array(
				'type' => 'text',
				'class' => 'form-control',
			),
		));

		$this->add(array(
			'name' => 'body',
			'attributes' => array(
				'type' => 'textarea',
				'class' => 'form-control',
			),
		));

		$this->add(array(
			'name' => 'submit',
			'attributes' => array(
				'type' => 'emailSubmit',
				'value' => 'Saada',
				'id' => 'submitbutton',
				'class' => 'btn btn-default',
			),
		));
		
	}
}