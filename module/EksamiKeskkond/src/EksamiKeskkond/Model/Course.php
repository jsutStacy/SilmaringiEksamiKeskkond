<?php

namespace EksamiKeskkond\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Course implements InputFilterAwareInterface {

	public $id;

	public $teacher_id;

	public $name;

	public $description;

	public $price;

	public $published;

	protected $inputFilter;

	public function exchangeArray($data) {
		$this->id = (isset($data['id'])) ? $data['id'] : null;
		$this->teacher_id = (isset($data['teacher_id'])) ? $data['teacher_id'] : null;
		$this->name = (isset($data['name'])) ? $data['name'] : null;
		$this->description = (isset($data['description'])) ? $data['description'] : null;
		$this->price = (isset($data['price'])) ? $data['price'] : null;
		$this->published = (isset($data['published'])) ? $data['published'] : null;
	}

	public function getArrayCopy() {
		return get_object_vars($this);
	}

	public function setInputFilter(InputFilterInterface $inputFilter) {
		throw new \Exception("Not used");
	}

	public function getInputFilter() {
		if (!$this->inputFilter) {
			$inputFilter = new InputFilter();
			$factory = new InputFactory();

			$inputFilter->add($factory->createInput(array(
				'name' => 'id',
				'required' => true,
				'filters' => array(
					array('name' => 'Int'),
				),
			)));

			$inputFilter->add($factory->createInput(array(
				'name' => 'name',
				'required' => true,
				'filters' => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
				),
				'validators' => array(
					array(
						'name' => 'StringLength',
						'options' => array(
							'encoding' => 'UTF-8',
							'min' => 1,
							'max' => 255,
						),
					),
				),
			)));

			$inputFilter->add($factory->createInput(array(
				'name' => 'price',
				'required' => true,
			)));

			$inputFilter->add($factory->createInput(array(
				'name' => 'teacher_id',
				'required' => false,
			)));

			$inputFilter->add($factory->createInput(array(
				'name' => 'published',
				'required' => true,
			)));

			$this->inputFilter = $inputFilter;
		}
		return $this->inputFilter;
	}
}