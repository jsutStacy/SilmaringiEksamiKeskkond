<?php

namespace EksamiKeskkond\Model;

class UserCourse {

	public $id;

	public $user_id;

	public $course_id;

	public $status;

	public function exchangeArray($data) {
		$this->id = (isset($data['id'])) ? $data['id'] : null;
		$this->user_id = (isset($data['user_id'])) ? $data['user_id'] : null;
		$this->course_id = (isset($data['course_id'])) ? $data['course_id'] : null;
		$this->status = (isset($data['status'])) ? $data['status'] : null;
	}
}