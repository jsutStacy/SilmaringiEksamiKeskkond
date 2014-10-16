<?php
namespace EksamiKeskkond\Form;

use Zend\Form\Form;

class RegisterForm extends Form {

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
			'name' => 'role_id',
			'attributes' => array(
				'type' => 'hidden',
			),
		));

		$this->add(array(
			'name' => 'firstname',
			'attributes' => array(
				'type' => 'text',
				'class' => 'form-control',
			),
			'options' => array(
				'label' => 'Eesnimi',
			),
		));

		$this->add(array(
			'name' => 'lastname',
			'attributes' => array(
				'type' => 'text',
				'class' => 'form-control',
			),
			'options' => array(
				'label' => 'Perekonnanimi',
			),
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
			'name' => 'password_confirm',
			'attributes' => array(
				'type' => 'password',
				'class' => 'form-control',
			),
			'options' => array(
				'label' => 'Parool uuesti',
			),
		));

		$this->add(array(
			'type' => 'Zend\Form\Element\Captcha',
			'name' => 'captcha',
			'options' => array(
				'label' => 'Palun kinnita, et oled inimene',
				'captcha' => new \Zend\Captcha\Figlet(),
			),
		));

		$this->add(array(
			'name' => 'submit',
			'attributes' => array(
				'type' => 'submit',
				'value' => 'Registreeri',
				'id' => 'submitbutton',
				'class' => 'btn btn-default',
			),
		));

	}
}