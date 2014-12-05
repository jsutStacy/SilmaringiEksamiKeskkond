<?php
namespace EksamiKeskkond\Form;

use Zend\Form\Form;

class HomeworkAnswerForm extends Form {

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
			'name' => 'user_id',
			'attributes' => array(
				'type' => 'hidden',
			),
		));

		$this->add(array(
			'name' => 'homework_id',
			'attributes' => array(
				'type' => 'hidden',
			),
		));

		$this->add(array(
			'name' => 'fileupload',
			'attributes' => array(
				'type' => 'file',
				'class' => 'form-control',
			),
		));

		$this->add(array(
			'name' => 'submit',
			'attributes' => array(
				'type' => 'submit',
				'value' => 'Esita',
				'id' => 'submitbutton',
				'class' => 'btn btn-default',
			),
		));
	}
}