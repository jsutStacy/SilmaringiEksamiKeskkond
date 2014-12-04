<?php

namespace EksamiKeskkond\Model;

class Subject {

	public $id;
	
	public $course_id;

	public $name;

	public $description;

	public function exchangeArray($data) {
		$this->id = (isset($data['id'])) ? $data['id'] : null;
		$this->course_id = (isset($data['course_id'])) ? $data['course_id'] : null;
		$this->name = (isset($data['name'])) ? $data['name'] : null;
		$this->description = (isset($data['description'])) ? $data['description'] : null;
	}

	public function getArrayCopy() {
		return get_object_vars($this);
	}
}