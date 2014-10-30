<?php

namespace EksamiKeskkond\Model;

class Course {

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
}