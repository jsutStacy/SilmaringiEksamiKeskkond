<?php
namespace EksamiKeskkond\Form;

use Zend\Form\Form;

class LoginForm extends Form {

	public function __construct($name = null) {

		// we want to ignore the name passed
		parent::__construct('EksamiKeskkond');

		$this->setAttributes(array(
			'method' => 'post',
			'class' => 'form-horizontal'
		));

		$this->add(array(
			'name' => 'email',
			'attributes' => array(
				'type' => 'email',
				'class' => 'form-control',
			),
			'options' => array(
				'label' => 'E-mail',
			),
		));

		$this->add(array(
			'name' => 'password',
			'attributes' => array(
				'type' => 'password',
				'class' => 'form-control',
			),
			'options' => array(
				'label' => 'Parool',
			),
		));

		$this->add(array(
			'name' => 'submit',
			'attributes' => array(
				'type' => 'submit',
				'value' => 'Logi sisse',
				'id' => 'submitbutton',
				'class' => 'btn btn-default',
			),
		));

	}
}