<?php

namespace Eksamikool\Model;

class Subsubject {

	public $id;
	
	public $subject_id;

	public $name;

	public function exchangeArray($data) {
		$this->id = (isset($data['id'])) ? $data['id'] : null;
		$this->subject_id = (isset($data['subject_id'])) ? $data['subject_id'] : null;
		$this->name = (isset($data['name'])) ? $data['name'] : null;
		$this->description = (isset($data['description'])) ? $data['description'] : null;

	}

	public function getArrayCopy() {
		return get_object_vars($this);
	}
}