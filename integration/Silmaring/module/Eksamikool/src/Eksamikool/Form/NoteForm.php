<?php
namespace Eksamikool\Form;

use Zend\Form\Form;

class NoteForm extends Form {

	public function __construct($name = null) {

		// we want to ignore the name passed
		parent::__construct('Eksamikool');

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
			'name' => 'lesson_id',
			'attributes' => array(
				'type' => 'hidden',
			),
		));

		$this->add(array(
			'name' => 'content',
			'attributes' => array(
				'type' => 'textarea',
				'class' => 'form-control',
			),
		));

		$this->add(array(
			'name' => 'submit',
			'attributes' => array(
				'type' => 'submit',
				'value' => 'Salvesta',
				'id' => 'submitbutton',
				'class' => 'btn btn-default',
			),
		));
		
	}
}