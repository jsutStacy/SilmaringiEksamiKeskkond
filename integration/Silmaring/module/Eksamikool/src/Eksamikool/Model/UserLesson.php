<?php

namespace Eksamikool\Model;

class UserLesson {

	public $id;

	public $user_id;

	public $course_id;

	public $done;

	public function exchangeArray($data) {
		$this->id = (isset($data['id'])) ? $data['id'] : null;
		$this->user_id = (isset($data['user_id'])) ? $data['user_id'] : null;
		$this->course_id = (isset($data['course_id'])) ? $data['course_id'] : null;
		$this->done = (isset($data['done'])) ? $data['done'] : null;
	}
}