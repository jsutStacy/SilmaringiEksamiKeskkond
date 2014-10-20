<?php

namespace EksamiKeskkond\Model;

use Zend\Db\TableGateway\TableGateway;

class UserTable {

	protected $tableGateway;

	public function __construct(TableGateway $tableGateway) {
		$this->tableGateway = $tableGateway;
	}

	public function fetchAll() {
		$resultSet = $this->tableGateway->select();

		return $resultSet;
	}

	public function getUser($id) {
		$id = (int) $id;

		$rowset = $this->tableGateway->select(array('id' => $id));
		$row = $rowset->current();

		if (!$row) {
			throw new \Exception("Could not find row $id");
		}
		return $row;
	}

	public function saveUser(User $user) {
		$data = array(
			'role_id' => $user->role_id,
			'firstname' => $user->firstname,
			'lastname' => $user->lastname,
			'email' => $user->email,
			'password' => $user->password,
			'status' => $user->status,
			'registration_date' => $user->registration_date,
		);

		$id = (int) $user->id;

		if ($id == 0) {
			$this->tableGateway->insert($data);
		}
		else {
			if ($this->getUser($id)) {
				$this->tableGateway->update($data, array('id' => $id));
			}
			else {
				throw new \Exception('Form id does not exist');
			}
		}
	}

	public function getUserByEmail($email) {
		$rowset = $this->tableGateway->select(array('email' => $email));
		$row = $rowset->current();

		if (!$row) {
			throw new \Exception("Could not find row $email");
		}
		return $row;
	}

	public function deleteUser($id) {
		$this->tableGateway->delete(array('id' => $id));
	}
}