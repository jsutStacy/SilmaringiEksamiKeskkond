<?php
namespace EksamiKeskkond\Form;

use Zend\Form\Form;

class CourseForm extends Form {

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
			'name' => 'name',
			'attributes' => array(
				'type' => 'text',
				'class' => 'form-control',
			),
			'options' => array(
				'label' => 'Kursuse nimi',
			),
		));

		$this->add(array(
			'name' => 'description',
			'attributes' => array(
				'type' => 'textarea',
				'class' => 'form-control',
			),
			'options' => array(
				'label' => 'Kursuse kirjeldus',
			),
		));

		$this->add(array(
			'name' => 'teacher_id',
			'type' => 'Zend\Form\Element\Select',
			'attributes' => array(
				'class' => 'form-control',
			),
			'options' => array(
				'label' => 'Kursuse õpetaja',
			),
		));

		$this->add(array(
			'name' => 'price',
			'attributes' => array(
				'type' => 'text',
				'class' => 'form-control',
			),
			'options' => array(
				'label' => 'Kursuse hind',
			),
		));

		$this->add(array(
			'type' => 'Zend\Form\Element\Select',
			'name' => 'published',
			'attributes' => array(
				'class' => 'form-control',
			),
			'options' => array(
				'label' => 'Kursuse nähtavus',
				'options' => array(
					'0' => 'Privaatne',
					'1' => 'Nähtav',
				),
			),
		));

		$this->add(array(
			'type' => 'Zend\Form\Element\Date',
			'name' => 'start_date',
			'options' => array(
				'label' => 'Kursuse algusaeg'
			),
			'attributes' => array(
				'min' => '2014-01-01',
				'max' => '2030-01-01',
				'step' => '1',
			)
		));

		$this->add(array(
				'type' => 'Zend\Form\Element\Date',
				'name' => 'end_date',
				'options' => array(
						'label' => 'Kursuse lõpuaeg'
				),
				'attributes' => array(
						'min' => '2014-01-01',
						'max' => '2030-01-01',
						'step' => '1',
				)
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