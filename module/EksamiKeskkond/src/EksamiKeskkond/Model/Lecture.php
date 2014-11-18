<?php

namespace EksamiKeskkond\Model;

class Lecture {

	public $id;

	public $subject_id;
	
	public $name;

	public $content;

	public $published;

	public function exchangeArray($data) {
		$this->id = (isset($data['id'])) ? $data['id'] : null;
		$this->subject_id = (isset($data['subject_id'])) ? $data['subject_id'] : null;
		$this->name = (isset($data['name'])) ? $data['name'] : null;
		$this->content = (isset($data['content'])) ? $data['content'] : null;
		$this->published = (isset($data['published'])) ? $data['published'] : null;
	}

	public function getArrayCopy() {
		return get_object_vars($this);
	}
}