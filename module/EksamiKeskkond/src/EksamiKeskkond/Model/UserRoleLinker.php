<?php

namespace EksamiKeskkond\Model;

class UserRoleLinker {

	public $user_id;

	public $role_id;

	public function exchangeArray($data) {
		$this->user_id = (isset($data['user_id'])) ? $data['user_id'] : null;
		$this->role_id = (isset($data['role_id'])) ? $data['role_id'] : null;
	}
}