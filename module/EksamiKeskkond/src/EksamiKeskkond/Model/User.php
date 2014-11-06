<?php

namespace EksamiKeskkond\Model;

class User {

	public $id;

	public $role_id;

	public $firstname;

	public $lastname;

	public $email;

	public $password;

	public $registration_date;

	public function exchangeArray($data) {
		$this->id = (isset($data['id'])) ? $data['id'] : null;
		$this->role_id = (isset($data['role_id'])) ? $data['role_id'] : null;
		$this->firstname = (isset($data['firstname'])) ? $data['firstname'] : null;
		$this->lastname = (isset($data['lastname'])) ? $data['lastname'] : null;
		$this->email = (isset($data['email'])) ? $data['email'] : null;
		$this->password = (isset($data['password'])) ? $data['password'] : null;
	}
}