<?php

namespace EksamiKeskkond\Model;

class HomeworkAnswers {

	public $id;

	public $homework_id;

	public $user_id;

	public $feedback;

	public $url;

	public function exchangeArray($data) {
		$this->id = (isset($data['id'])) ? $data['id'] : null;
		$this->homework_id = (isset($data['homework_id'])) ? $data['homework_id'] : null;
		$this->user_id = (isset($data['user_id'])) ? $data['user_id'] : null;
		$this->feedback = (isset($data['feedback'])) ? $data['feedback'] : null;
		$this->url = (isset($data['url'])) ? $data['url'] : null;
	}

	public function getArrayCopy() {
		return get_object_vars($this);
	}
}