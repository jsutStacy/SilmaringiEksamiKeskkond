<?php

namespace Eksamikool\Model;

class Lesson {

	public $id;

	public $subsubject_id;

	public $name;

	public $content;

	public $published;
	
	public $type;

	public function exchangeArray($data) {
		$this->id = (isset($data['id'])) ? $data['id'] : null;
		$this->subsubject_id = (isset($data['subsubject_id'])) ? $data['subsubject_id'] : null;
		$this->name = (isset($data['name'])) ? $data['name'] : null;
		$this->content = (isset($data['content'])) ? $data['content'] : null;
		$this->published = (isset($data['published'])) ? $data['published'] : null;
		$this->type = (isset($data['type'])) ? $data['type'] : null;
	}

	public function getArrayCopy() {
		return get_object_vars($this);
	}
}