<?php

namespace Eksamikool\Model;

class Homework {

	public $id;

	public $subsubject_id;

	public $user_id;

	public $description;

	public $url;

	public function exchangeArray($data) {
		$this->id = (isset($data['id'])) ? $data['id'] : null;
		$this->subsubject_id = (isset($data['subsubject_id'])) ? $data['subsubject_id'] : null;
		$this->user_id = (isset($data['user_id'])) ? $data['user_id'] : null;
		$this->description = (isset($data['description'])) ? $data['description'] : null;
		$this->url = (isset($data['url'])) ? $data['url'] : null;
	}

	public function getArrayCopy() {
		return get_object_vars($this);
	}
}