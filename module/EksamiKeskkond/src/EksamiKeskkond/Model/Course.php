<?php

namespace EksamiKeskkond\Model;

class Course {

	public $id;

	public $teacher_id;

	public $name;

	public $description;

	public $price;

	public $published;

	public $start_date;

	public $end_date;

	protected $inputFilter;

	public function exchangeArray($data) {
		$this->id = (isset($data['id'])) ? $data['id'] : null;
		$this->teacher_id = (isset($data['teacher_id'])) ? $data['teacher_id'] : null;
		$this->name = (isset($data['name'])) ? $data['name'] : null;
		$this->description = (isset($data['description'])) ? $data['description'] : null;
		$this->price = (isset($data['price'])) ? $data['price'] : null;
		$this->start_date = (isset($data['start_date'])) ? $data['start_date'] : null;
		$this->end_date = (isset($data['end_date'])) ? $data['end_date'] : null;
		$this->published = (isset($data['published'])) ? $data['published'] : null;
	}

	public function getArrayCopy() {
		return get_object_vars($this);
	}
}