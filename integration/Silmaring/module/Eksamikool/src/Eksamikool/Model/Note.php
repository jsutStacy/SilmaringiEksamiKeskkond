<?php

namespace Eksamikool\Model;

class Note {

	public $id;

	public $user_id;

	public $lesson_id;

	public $content;

	public function exchangeArray($data) {
		$this->id = (isset($data['id'])) ? $data['id'] : null;
		$this->user_id = (isset($data['user_id'])) ? $data['user_id'] : null;
		$this->lesson_id = (isset($data['lesson_id'])) ? $data['lesson_id'] : null;
		$this->content = (isset($data['content'])) ? $data['content'] : null;
	}

	public function getArrayCopy() {
		return get_object_vars($this);
	}
}