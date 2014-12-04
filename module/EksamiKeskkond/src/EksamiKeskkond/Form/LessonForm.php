<?php
namespace EksamiKeskkond\Form;

use Zend\Form\Form;

class LessonForm extends Form {

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
			'name' => 'subsubject_id',
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
			'type' => 'Zend\Form\Element\Select',
			'name' => 'published',
			'attributes' => array(
				'class' => 'form-control',
			),
			'options' => array(
				'label' => 'Tunni nähtavus',
				'options' => array(
					'0' => 'Privaatne',
					'1' => 'Nähtav',
				),
			),
		));

		$this->add(array(
			'name' => 'url',
			'attributes' => array(
				'type' => 'Url',
				'class' => 'form-control',
			),
			'options' => array(
				'label' => 'Url',
			),
		));

		$this->add(array(
			'name' => 'fileupload',
			'attributes' => array(
				'type' => 'file',
				'multiple' => true,
			),
		));

		$this->add(array(
			'name' => 'user_id',
			'attributes' => array(
				'type' => 'hidden',
			),
		));

		$this->add(array(
			'name' => 'lesson_files_id',
			'attributes' => array(
				'type' => 'hidden',
			),
		));

		$this->add(array(
			'type' => 'Zend\Form\Element\Select',
			'name' => 'type',
			'attributes' => array(
				'class' => 'form-control',
			),
			'options' => array(
				'label' => 'Tüüp',
				'options' => array(
					'text' => 'Tekst',
					'video' => 'Video',
					'presentation' => 'Esitlus',
					'audio' => 'Audio',
					'images' => 'Pildid',
				),
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