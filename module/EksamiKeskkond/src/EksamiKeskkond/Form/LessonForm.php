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
		));

		$this->add(array(
			'name' => 'content',
			'attributes' => array(
				'type' => 'textarea',
				'class' => 'form-control',
			),
		));

		$this->add(array(
			'type' => 'Select',
			'name' => 'published',
			'attributes' => array(
				'class' => 'form-control',
			),
			'options' => array(
				'options' => array(
					'0' => array(
						'value' => '0',
						'label' => 'Privaatne',
						'selected' => true,
					),
					'1' => array(
						'value' => '1',
						'label' => 'Nähtav',
					),
				),
			),
		));

		$this->add(array(
			'name' => 'url',
			'attributes' => array(
				'type' => 'Url',
				'class' => 'form-control',
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
			'type' => 'Select',
			'name' => 'type',
			'attributes' => array(
				'class' => 'form-control',
			),
			'options' => array(
				'options' => array(
					'text' => array(
						'value' => 'text',
						'label' => 'Tekst',
						'selected' => true,
					),
					'video' => array(
						'value' => 'video',
						'label' => 'Video',
					),
					'presentation' => array(
						'value' => 'presentation',
						'label' => 'Esitlus',
					),
					'audio' => array(
						'value' => 'audio',
						'label' => 'Audio',
					),
					'images' => array(
						'value' => 'images',
						'label' => 'Pildid',
					),
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