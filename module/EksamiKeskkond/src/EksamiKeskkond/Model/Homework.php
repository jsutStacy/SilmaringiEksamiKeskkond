<?php

namespace EksamiKeskkond\Model;

class Homework {

	public $id;

	public $lesson_id;

	public $user_id;

	public $url;

	public $feedback;

	public function exchangeArray($data) {
		$this->id = (isset($data['id'])) ? $data['id'] : null;
		$this->lesson_id = (isset($data['lesson_id'])) ? $data['lesson_id'] : null;
		$this->user_id = (isset($data['user_id'])) ? $data['user_id'] : null;
		$this->url = (isset($data['url'])) ? $data['url'] : null;
		$this->feedback = (isset($data['feedback'])) ? $data['feedback'] : null;
	}

	public function getArrayCopy() {
		return get_object_vars($this);
	}
}